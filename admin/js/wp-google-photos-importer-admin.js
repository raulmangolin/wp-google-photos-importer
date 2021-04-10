(function ($) {
  'use strict';

  let albumPhotoIds = [];
  let canImport = true;

  $(window).load(function () {
    let clientToken = $('#wp-gpi-clientToken').val();
    if (clientToken) {
      request_data(clientToken)
    }

    $('#wp-gpi-loadmore').on('click', function (e) {
      e.preventDefault();
      let pageNext = $(this).data('nextToken');
      let clientToken = $('#wp-gpi-clientToken').val();
      request_data(clientToken, pageNext);
    });

    $('#wp-gpi-loadmore-photos').on('click', function (e) {
      e.preventDefault();
      let albumId = $(this).data('albumId');
      let pageNext = $(this).data('nextToken');
      let clientToken = $('#wp-gpi-clientToken').val();
      request_data_photos(albumId, clientToken, pageNext);
    });

    $('#wp-gpi-import-photos').unbind('click').on('click', function (e) {
      e.preventDefault();
      $('#wp-gpi-import-photos').unbind('click');
      $('#wp-gpi-import-photos').text('Importing...');
      $('#wp-gpi-import-photos').attr('disabled', 'disabled');

      let clientToken = $('#wp-gpi-clientToken').val();
      import_photos(clientToken);
    });
  });

  function import_photos(clientToken) {
    $(albumPhotoIds).each(function (i, el) {
      post_import_photos(clientToken, el);
    });
  }

  function post_import_photos(clientToken, el) {
    if (canImport) {
      canImport = false;

      let data = {
        'action': 'admin_add_google_photos_albums_photos',
        'mediaId': el,
        'access_token': clientToken,
      };

      let li = '<li>Importing ' + el + '...</li>';
      $('.wp-gpi-importing-list').append(li);

      $.post(ajaxurl, data, function (response) {
      }).done(function (response) {
        let li = '<li>Imported ' + el + '.</li>';
        $('.wp-gpi-importing-list').append(li);

        canImport = true
      });
    } else {
      setTimeout(function () {
        post_import_photos(clientToken, el);
      }, 500);
    }
  }

  function bind_click_album() {
    $('#wp-gpi-albums li').unbind('click').on('click', function (e) {
      e.preventDefault();

      remove_active_album()
      $(this).find('.img-cover').addClass('active');

      let albumId = $(this).data('album-id');
      let clientToken = $('#wp-gpi-clientToken').val();

      albumPhotoIds = [];
      $('#wp-gpi-albums-photos').empty();
      request_data_photos(albumId, clientToken, null, true)
    });
  }

  function bind_click_album_photos() {
    $('#wp-gpi-albums-photos li').unbind('click').on('click', function (e) {
      e.preventDefault();

      let albumPhotoId = $(this).data('album-photo-id');
      let index = albumPhotoIds.indexOf(albumPhotoId);
      if (index === -1) {
        $(this).find('.img-cover').addClass('active');
        albumPhotoIds.push(albumPhotoId);
      } else {
        $(this).find('.img-cover').removeClass('active');
        albumPhotoIds.splice(index, 1);
      }

      let importCount = albumPhotoIds.length;
      $('.wp-gpi-total-import').text(importCount);
      if (importCount > 0) {
        $('#wp-gpi-import-photos').show();
      } else {
        $('#wp-gpi-import-photos').hide();
      }
    });
  }

  function remove_active_album() {
    $('#wp-gpi-albums li').find('.img-cover').removeClass('active');
  }

  function request_data(clientToken, nextPage = null) {
    let loadMore = $('#wp-gpi-loadmore');
    let loading = $('.wp-gpi-loading');

    $(loading).show();

    let data = {
      'action': 'admin_get_google_photos_albums',
      'access_token': clientToken,
      'nextPage': nextPage
    };

    $.post(ajaxurl, data, function (response) {
      if (response && response.albums) {
        for (let i = 0; i < response.albums.length; i++) {
          let li = create_album_element(response.albums[i]);
          $(li).hide().appendTo('#wp-gpi-albums').show('normal');
        }

        $(loading).hide();

        if (response.next) {
          $(loadMore).data('nextToken', response.next);
          $(loadMore).show();
        } else {
          $(loadMore).hide();
        }

        bind_click_album();
      }
    });
  }

  function request_data_photos(albumId, clientToken, nextPage = null, first = false) {
    let loadMore = $('#wp-gpi-loadmore-photos');
    let loading = $('.wp-gpi-loading');

    $(loading).show();

    let data = {
      'action': 'admin_get_google_photos_albums_photos',
      'albumId': albumId,
      'access_token': clientToken,
      'nextPage': nextPage
    };

    $.post(ajaxurl, data, function (response) {
      if (response && response.photos) {
        for (let i = 0; i < response.photos.length; i++) {
          let li = create_album_photo_element(response.photos[i]);
          $(li).hide().appendTo('#wp-gpi-albums-photos').show('normal');
        }

        $(loading).hide();

        if (response.next) {
          $(loadMore).data('nextToken', response.next);
          $(loadMore).data('albumId', albumId);
          $(loadMore).show();
        } else {
          $(loadMore).hide();
        }

        bind_click_album_photos();

        if (first) {
          $([document.documentElement, document.body]).animate({
            scrollTop: $("#wp-gpi-albums-photos").offset().top
          }, 1000);
        }
      }
    });
  }

  function create_album_element(el) {
    let li = '';

    li += '<li data-album-id="' + el.id + '">';
    li += ' <div class="img-cover">';
    li += '   <div class="over"><span>Select Album</span></div>';
    li += '   <img src="' + el.coverPhotoBaseUrl + '">';
    li += ' </div>';
    li += ' <span class="album-name">' + el.title + '</span>';
    li += ' <a href="' + el.productUrl + '" target="_blank">View on Google Photos</a>';
    li += '</li>';

    return li;
  }

  function create_album_photo_element(el) {
    let li = '';

    li += '<li data-album-photo-id="' + el.id + '">';
    li += ' <div class="img-cover">';
    li += '   <div class="over"><span>Select Photo</span></div>';
    li += '   <img src="' + el.baseUrl + '">';
    li += ' </div>';
    li += ' <a href="' + el.productUrl + '" target="_blank">View on Google Photos</a>';
    li += '</li>';

    return li;
  }
})(jQuery);