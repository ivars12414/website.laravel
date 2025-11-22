<?php $host = getDomainWithPrefix(); ?>
<?php $fontFolder = "$host/modules/pdf_html/dm_sans"; ?>

/* latin-ext */
@font-face {
    font-family: 'DM Sans';
    font-style: normal;
    font-weight: 400;
    font-display: swap;
    src: url(<?= $fontFolder ?>/regular.ttf) format('truetype');
    unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}

/* latin-ext */
@font-face {
    font-family: 'DM Sans';
    font-style: normal;
    font-weight: 500;
    font-display: swap;
    src: url(<?= $fontFolder ?>/medium.ttf) format('truetype');
    unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}

/* latin-ext */
@font-face {
    font-family: 'DM Sans';
    font-style: normal;
    font-weight: 600;
    font-display: swap;
    src: url(<?= $fontFolder ?>/semi_bold.ttf) format('truetype');
    unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}

/* latin-ext */
@font-face {
    font-family: 'DM Sans';
    font-style: normal;
    font-weight: 700;
    font-display: swap;
    src: url(<?= $fontFolder ?>/bold.ttf) format('truetype');
    unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
