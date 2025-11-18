class DropZone {
  constructor(options) {
    this.config = {
      maxFiles: options.maxFiles || 1,
      maxFileSize: (options.maxFileSize || 2) * 1024 * 1024, // 2 MB
      uploadUrl: options.uploadUrl || '',
      deleteUrl: options.deleteUrl || '',
      usePhpHandler: !!(options.uploadUrl),
      entityId: options.entityId || null, // ID сущности для которой загружаем (для пхп обработчика)
      selector: options.selector,
      dropzoneSelector: options.dropzoneSelector || null,
      previewSelector: options.previewSelector || null,
      previewTemplate: options.previewTemplate || this.defaultPreviewTemplate,
      hideZoneOnLimitReached: options.hideZoneOnLimitReached || true,
      allowedExtensions: options.allowedExtensions || ['jpg', 'jpeg', 'png', 'gif'],
      onUploaded: options.onUploaded || null,
      onRemoved: options.onRemoved || null,
    };

    this.fileInput = $(this.config.selector).hide();

    this.filesCount = 0;

    // Создание дефолтных блоков, если не указаны селекторы
    this.dropzone = this.config.dropzoneSelector ? $(this.config.dropzoneSelector) : $('<div>', {
      class: 'dropzone',
      text: 'Перетащите файлы сюда или нажмите, чтобы выбрать',
    });
    this.previewContainer = this.config.previewSelector
      ? $(this.config.previewSelector)
      : $('<div>', { class: 'preview' });

    // Устанавливаем атрибут accept на input в зависимости от разрешенных расширений
    this.setInputAcceptAttribute();

    this.filesArray = [];

    this.init();
  }

  init() {
    if (!this.config.dropzoneSelector) {
      this.fileInput.before(this.dropzone);
    }
    if (!this.config.previewSelector) {
      this.dropzone.after(this.previewContainer);
    }

    this.dropzone.on('click', () => this.fileInput.click());
    this.dropzone.on('dragover', (e) => this.handleDragOver(e));
    this.dropzone.on('dragleave', (e) => this.handleDragLeave(e));
    this.dropzone.on('drop', (e) => this.handleDrop(e));
    this.fileInput.on('change', (e) => this.handleFiles(e.target.files));
    this.previewContainer.on('click', '[data-remove-file]', (e) => this.removeFile(e));
  }

  setInputAcceptAttribute() {
    const accept = this.config.allowedExtensions.map(ext => {
      switch (ext) {
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
          return `image/${ext}`;
        case 'pdf':
          return 'application/pdf';
        case 'mp4':
          return 'video/mp4';
        default:
          return `.${ext}`; // Для остальных расширений
      }
    }).join(',');

    this.fileInput.attr('accept', accept);
  }

  handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    this.dropzone.addClass('dragging');
  }

  handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    this.dropzone.removeClass('dragging');
  }

  handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    this.dropzone.removeClass('dragging');
    this.handleFiles(e.originalEvent.dataTransfer.files);
  }

  handleFiles(files) {
    if (this.config.hideZoneOnLimitReached) {
      this.dropzone.show();
    }
    for (let file of files) {
      const fileExtension = file.name.split('.').pop().toLowerCase();

      if (!this.config.allowedExtensions.includes(fileExtension)) {
        alert(`File ${file.name} extension not allowed`);
        continue;
      }

      if (file.size > this.config.maxFileSize) {
        const maxMB = this.config.maxFileSize / 1024 / 1024;
        alert(`File ${file.name} is too large (max ${maxMB} MB)`);
        continue;
      }
      this.filesArray.push(file);
      this.filesCount++;
      this.previewContainer.html('');
      for (let file2 of this.filesArray) {
        this.previewImage(file2);
      }
      if (this.filesCount >= this.config.maxFiles) {
        if (this.config.hideZoneOnLimitReached) {
          this.dropzone.hide();
        } else {
          alert(`Max files (${this.config.maxFiles}) reached`);
        }
        break;
      }
    }

    if (this.config.usePhpHandler) {
      this.uploadFiles(this.filesArray);
    } else {
      this.insertFilesToInput(this.filesArray);
      if (this.config.onUploaded) {
        this.config.onUploaded();
      }
    }
  }

  previewImage(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      const previewHtml = this.getPreviewHTML(file, e.target.result);
      this.previewContainer.append(previewHtml);
    };
    reader.readAsDataURL(file);
  }

  getPreviewHTML(file, src) {
    let previewContent;
    if (file.type.startsWith('image/')) {
      previewContent = `<img src="${src}" alt="${file.name}">`;
    } else if (file.type === 'application/pdf') {
      previewContent = `<iframe src="${src}"></iframe>`;
    } else if (file.type.startsWith('video/')) {
      previewContent = `<video controls><source src="${src}" type="${file.type}"></video>`;
    } else {
      previewContent = `<span>${file.name}</span>`;
    }

    return this.config.previewTemplate(file, previewContent);
  }

  removeFile(e) {
    const fileName = $(e.target).data('removeFile');
    const prevCnt = this.filesArray.length;
    this.filesArray = this.filesArray.filter(file => file.name !== fileName);
    const newCnt = this.filesArray.length;
    this.filesCount -= prevCnt - newCnt;
    $(e.target).closest('.preview-item').remove();

    if (this.config.usePhpHandler) {
      this.deleteFile(fileId);
    } else {
      this.insertFilesToInput(this.filesArray);
    }
    if (this.filesCount < this.config.maxFiles) {
      this.dropzone.show();
    }

    if (this.config.onRemoved) {
      this.config.onRemoved();
    }
  }

  uploadFiles(files) {
    let formData = new FormData();
    formData.append('entity_id', this.config.entityId); // Передаем ID сущности
    for (let file of files) {
      formData.append('files[]', file);
    }
    $.ajax({
      url: this.config.uploadUrl,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: (response) => {
        const res = JSON.parse(response);
        res.forEach(fileInfo => {
          if (fileInfo.status === 'success') {
            // Добавляем ID файла к кнопке удаления
            this.previewContainer.find(`[data-remove-file="${fileInfo.file_name}"]`)
              .data('file-id', fileInfo.file_id);
          }
        });
        console.log('Файлы успешно загружены');
        console.log(response);

        if (this.config.onUploaded) {
          this.config.onUploaded();
        }
      },
      error: function(jqXHR, textStatus, errorMessage) {
        console.error('Ошибка загрузки файлов:', errorMessage);
      },
    });
  }

  deleteFile(fileId) {
    $.ajax({
      url: this.config.deleteUrl,
      type: 'POST',
      data: { file_id: fileId },
      success: function(response) {
        console.log('Файл успешно удален');
        console.log(response);
      },
      error: function(jqXHR, textStatus, errorMessage) {
        console.error('Ошибка удаления файла:', errorMessage);
      },
    });
  }

  insertFilesToInput(files) {
    const dataTransfer = new DataTransfer();
    for (let file of files) {
      dataTransfer.items.add(file);
    }
    this.fileInput[0].files = dataTransfer.files;
  }

  preloadFiles(preloadedFiles) {
    preloadedFiles.forEach(fileInfo => {
      if (this.filesCount < this.config.maxFiles) {
        const file = new File([], fileInfo.name, { type: fileInfo.type || 'image/jpeg' });
        this.filesCount++;
        const previewHtml = this.getPreviewHTML(file, fileInfo.path);
        this.previewContainer.append(previewHtml);
        // Добавляем ID файла к кнопке удаления
        this.previewContainer.find(`[data-remove-file="${fileInfo.name}"]`)
          .data('file-id', fileInfo.id).append(`<input type="hidden" name="existing_files[]" value="${fileInfo.id}"/>`);
        if (this.filesCount >= this.config.maxFiles) {
          if (this.config.hideZoneOnLimitReached) {
            this.dropzone.hide();
          }
        }
      }
    });

    // if (!this.config.usePhpHandler) {
    //   this.insertFilesToInput(this.filesArray);
    // }
  }

  defaultPreviewTemplate(file, previewContent) {
    return `
            <div class="preview-item">
                ${previewContent}
                <button type="button" class="remove-btn" data-remove-file="${file.name}">X</button>
            </div>
        `;
  }
}
