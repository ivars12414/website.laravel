<form action="" class="intro__form js-search-form" data-form="search">
  <div class="container">
    <div class="intro__form-wrapper">
      <div class="intro__form-block">
        <div class="intro__form-column">
          <label class="label">{!! returnWord("Category", WORDS_INTERFACE) !!}</label>

          <select name="category_hash" class="js-select"
                  data-placeholder="{!! returnWord("Choose category", WORDS_INTERFACE) !!}">
            <option value="any">{!! returnWord("Any", WORDS_INTERFACE) !!}</option>
            @foreach($filterCategories as $filterCategory)
              <option value="{{ $filterCategory->hash }}"
                      {{ !empty($currentCategory) && $currentCategory->hash == $filterCategory->hash ? 'selected' : '' }}>
                {{ $filterCategory->name }}
              </option>
            @endforeach
          </select>
        </div>
        <?php

        /*$query = "
              SELECT v.* FROM {$table_prefix}attributes_values v
              LEFT JOIN {$table_prefix}attributes a ON v.parent_hash = a.hash AND a.lang_id = '$langId'
              WHERE 1
                AND v.deleted = '0'
                AND a.deleted = '0'
                AND a.in_filter = '1'
                AND v.lang_id = '$langId'
                AND EXISTS (SELECT id FROM {$table_prefix}items_attributes_values WHERE attribute_hash=a.hash AND value_hash=v.hash LIMIT 1)
              ORDER BY a.ord, v.ord
            ";
        $result = mysql_query($query);
        $attributes = [];
        while ($row = mysql_fetch_array($result)) {
          $attributes[$row['parent_hash']][] = $row;
          unset($row['attribute']);
        }
        ?>
        <?php foreach ($attributes as $attributeHash => $values) {
          $attributeData = \Website\Inc\AttributesCache::getByHash($attributeHash, $langId);
          $isAttributeInGet = !empty($_GET[$attributeData['code']]);
          ?>
        <div class="form__group">
          <div class="form__title">
              <?= $attributeData['name'] ?>
          </div>
          <div class="select">
            <select name="attributes_values[<?= $attributeHash ?>]" class="js-select"
                    data-placeholder="<?php getword("Any", WORDS_INTERFACE); ?>">
              <option value="any"><?php getword("Any", WORDS_INTERFACE); ?></option>
                <?php foreach ($values as $value) {
                $selected = '';
                if ($isAttributeInGet) {
                  $valueData = \Website\Inc\AttributesValuesCache::getByHash($value['hash'], $langId);
                  if ($_GET[$attributeData['code']] == $valueData['code']) {
                    $selected = 'selected';
                  }
                }
                ?>
              <option value="<?= $value['hash'] ?>" <?= $selected ?>><?= $value['name'] ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
        <?php }*/ ?>
      </div>

      <div class="intro__form-block">
        <button type="submit" class="btn btn--main">
          {!! returnWord("Find", WORDS_INTERFACE) !!}
        </button>
      </div>
    </div>
  </div>
</form>

<script>
  $(function () {
    new FormSubmit({
      formSelector: '.js-search-form',
      ajaxUrl: '/modules/catalog/search/ajax/handler.php?lid={{ lang()->id }}',
      showAlertOnSuccess: false,
      successCallback: (response) => {
        if (!response.error) {
          if (response.href) {
            location.href = response.href;
          }
        }
      },
    });
  })
</script>