(function ($) {
    'use strict';

    $(document).ready(function () {
        const rcilModal = $('#rcil-modal');
        const rcilOpenBtn = $('#rcil-open-modal');
        const rcilCloseBtn = $('.rcil-close');
        const rcilForm = $('#rcil-selection-form');
        const rcilSubmitBtn = $('#rcil-submit-selection');
        const rcilTotalAmount = $('#rcil-total-amount');

        // Integration: Red Cultural Pages (custom templates)
        // Insert "Buy Some Lessons" button into the course sidebar card if the template exists.
        const rcpCourseCardBody = $('#red-cultural-course-card-body');
        if (rcpCourseCardBody.length && rcilModal.length && !$('#rcil-open-modal').length) {
            const $btn = $('<button id="rcil-open-modal" type="button" class="rcil-rcp-buy-btn"></button>');
            $btn.text((rcil_params && rcil_params.buy_some_lessons_label) ? rcil_params.buy_some_lessons_label : 'Buy Some Lessons');

            // Try to place it before the "Incluye curso" section.
            const includes = rcpCourseCardBody.find('#red-cultural-course-includes');
            if (includes.length) {
                $btn.insertBefore(includes);
            } else {
                rcpCourseCardBody.append($btn);
            }
        }

        // Integration: reuse the course "Lista de alumnos" button as the alumni trigger.
        const rcpStudentsBtn = $('#red-cultural-course-students');
        if (rcpStudentsBtn.length) {
            if (rcil_params && rcil_params.can_view_alumni) {
                rcpStudentsBtn.on('click', function (e) {
                    const alumniModal = $('#rcil-alumni-modal');
                    if (alumniModal.length) {
                        e.preventDefault();
                        alumniModal.fadeIn();
                    }
                });
            } else {
                rcpStudentsBtn.hide();
            }
        }

        function rcilCollectAllLessonsForCoursePurchase() {
            const selectedLessons = [];
            $('input[name="rcil_lessons[]"]').each(function () {
                const $el = $(this);
                const id = $el.val();
                const title = $el.data('title') || '';
                if (!id) return;
                selectedLessons.push({ id: id, title: title });
            });
            return selectedLessons;
        }

        // Integration: "COMPRAR CURSO" should add the full-course product to cart and redirect to checkout.
        $(document).on('click', '#rcil-buy-course', function (e) {
            if (!rcilModal.length) return;
            e.preventDefault();

            const selectedLessons = rcilCollectAllLessonsForCoursePurchase();
            if (!selectedLessons.length) return;

            const $btn = $(this);
            $btn.prop('disabled', true);
            const prevText = $btn.text();
            $btn.text(rcil_params.buying_label || 'Processing...');

            $.ajax({
                type: 'POST',
                url: rcil_params.ajax_url,
                data: {
                    action: 'rcil_create_individual_lesson_product',
                    nonce: rcil_params.nonce,
                    course_id: rcil_params.course_id,
                    lessons: selectedLessons,
                    is_full_course: 1
                },
                success: function (response) {
                    if (response && response.success && response.data && response.data.redirect_url) {
                        window.location.href = response.data.redirect_url;
                        return;
                    }
                    alert((response && response.data) ? response.data : 'Error creating selection.');
                },
                error: function () {
                    alert(rcil_params.server_error_label || 'Server error.');
                },
                complete: function () {
                    $btn.prop('disabled', false).text(prevText);
                }
            });
        });

        // Open
        $(document).on('click', '#rcil-open-modal, .rcil-buy-lessons-btn', function (e) {
            const modal = $('#rcil-modal');
            if (!modal.length) return;
            e.preventDefault();
            modal.fadeIn();
        });

        // Close
        $(document).on('click', '.rcil-close', function () {
            $('#rcil-modal').fadeOut();
        });

        $(window).on('click', function (e) {
            const modal = $('#rcil-modal');
            if ($(e.target).is(modal)) {
                modal.fadeOut();
            }
        });

        if (rcilModal.length) {
            // Real-time calculation
            const unitPrice = parseInt(rcilTotalAmount.data('unit-price')) || 0;
            const fullCoursePrice = parseInt(rcilModal.attr('data-full-price')) || 0;

            $('input[name="rcil_lessons[]"]').on('change', function () {
                const $allCheckboxes = $('input[name="rcil_lessons[]"]');
                const $selectedCheckboxes = $allCheckboxes.filter(':checked');

                const totalInForm = $allCheckboxes.length;
                const selectedCount = $selectedCheckboxes.length;

                let total = 0;
                let isFullCourse = false;

                // Full course price ONLY when every checkbox is selected (including already-owned disabled ones).
                if (totalInForm > 0 && selectedCount === totalInForm && fullCoursePrice > 0) {
                    total = fullCoursePrice;
                    isFullCourse = true;
                    rcilSubmitBtn.text(rcil_params.buy_course_label);
                } else {
                    total = selectedCount * unitPrice;
                    rcilSubmitBtn.text(rcil_params.buy_lessons_label);
                }

                rcilTotalAmount.text(total.toLocaleString());
                rcilSubmitBtn.prop('disabled', selectedCount === 0);

                rcilForm.data('is-full-course', isFullCourse);
            });

            // Submit selection via AJAX
            rcilForm.on('submit', function (e) {
                e.preventDefault();

                const selectedLessons = [];
                $('input[name="rcil_lessons[]"]:checked:not(:disabled)').each(function () {
                    selectedLessons.push({
                        id: $(this).val(),
                        title: $(this).data('title')
                    });
                });

                if (selectedLessons.length === 0) return;

                rcilSubmitBtn.prop('disabled', true).text(rcil_params.buying_label);

                $.ajax({
                    type: 'POST',
                    url: rcil_params.ajax_url,
                    data: {
                        action: 'rcil_create_individual_lesson_product',
                        nonce: rcil_params.nonce,
                        course_id: rcil_params.course_id,
                        lessons: selectedLessons,
                        is_full_course: rcilForm.data('is-full-course') ? 1 : 0
                    },
                    success: function (response) {
                        if (response.success && response.data.redirect_url) {
                            window.location.href = response.data.redirect_url;
                        } else {
                            alert(response.data || 'Error creating selection.');
                            location.reload(); // Refresh to update any state if needed
                        }
                    },
                    error: function () {
                        alert(rcil_params.server_error_label);
                        rcilSubmitBtn.prop('disabled', false).text(rcil_params.buy_lessons_label);
                    }
                });
            });
        }
        // Alumni Modal Logic
        const alumniModal = $('#rcil-alumni-modal');
        const alumniCloseBtn = $('.rcil-alumni-close');
        const alumniSelect = $('#rcil-alumni-lesson-select');
        const alumniResults = $('#rcil-alumni-results');

        if (alumniModal.length) {
            // Because button might be appended via hooks multiple times
            $(document).on('click', '#rcil-open-alumni-modal', function (e) {
                e.preventDefault();
                alumniModal.fadeIn();
            });

            alumniCloseBtn.on('click', function () {
                alumniModal.fadeOut();
            });

            $(window).on('click', function (e) {
                if ($(e.target).is(alumniModal)) {
                    alumniModal.fadeOut();
                }
            });

            alumniSelect.on('change', function() {
                const lessonId = $(this).val();
                if (!lessonId) {
                    alumniResults.empty();
                    return;
                }

                alumniResults.html('<p>' + rcil_params.loading_label + '</p>');

                $.ajax({
                    type: 'POST',
                    url: rcil_params.ajax_url,
                    data: {
                        action: 'rcil_get_alumni_list',
                        nonce: rcil_params.nonce,
                        course_id: rcil_params.course_id,
                        lesson_id: lessonId
                    },
                    success: function (response) {
                        if (response.success) {
                            var html = '';
                            if (response.data.alumni.length === 0) {
                                html = '<p>' + rcil_params.no_alumni_label + '</p>';
                            } else {
                                html = '<table style="width: 100%; text-align: left; border-collapse: collapse; margin-top: 10px;">';
                                html += '<thead><tr><th style="border-bottom: 2px solid #ddd; padding: 8px;">' + rcil_params.name_label + '</th><th style="border-bottom: 2px solid #ddd; padding: 8px;">' + rcil_params.email_label + '</th></tr></thead>';
                                html += '<tbody>';
                                $.each(response.data.alumni, function(i, a) {
                                    html += '<tr><td style="border-bottom: 1px solid #eee; padding: 8px;">' + a.name + '</td><td style="border-bottom: 1px solid #eee; padding: 8px;">' + a.email + '</td></tr>';
                                });
                                html += '</tbody></table>';
                                html += '<p style="margin-top: 15px; font-weight: bold; background: #f9f9f9; padding: 10px; border-radius: 4px;">' + rcil_params.total_students_label + response.data.alumni.length + '</p>';
                            }
                            alumniResults.html(html);
                        } else {
                            alumniResults.html('<p style="color:red;">' + (response.data || 'Error loading alumni list.') + '</p>');
                        }
                    },
                    error: function () {
                        alumniResults.html('<p style="color:red;">' + rcil_params.server_error_label + '</p>');
                    }
                });
            });
        }

        // Admin-only: inline "Video Progression URL" editor on course page.
        function rcilNormalizeUrl(url) {
            if (!url) return '';
            try {
                const u = new URL(url, window.location.origin);
                return u.origin + u.pathname.replace(/\/$/, '');
            } catch (e) {
                return String(url).replace(/[?#].*$/, '').replace(/\/$/, '');
            }
        }

        function rcilGetLessonEntryFromHref(href) {
            if (!rcil_params || !rcil_params.lesson_video_map) return null;
            const key = rcilNormalizeUrl(href);
            return rcil_params.lesson_video_map[key] || rcil_params.lesson_video_map[href] || null;
        }

        function rcilEnsureEditor($preview, entry, $a) {
            let $editor = $preview.is('.rcp-lesson-card') ? $preview.find('> .rcil-video-editor') : $preview.children('.rcil-video-editor');
            if ($editor.length) return $editor;

            const value = (entry && entry.video_url) ? entry.video_url : '';
            const zoomValue = (entry && entry.zoom_url) ? entry.zoom_url : '';
            const availableValue = (entry && entry.available_from_iso) ? entry.available_from_iso : '';
            const title = (entry && entry.title) ? entry.title : '';
            $editor = $(
                '<div class="rcil-video-editor" data-lesson-id="' + entry.id + '">' +
                    '<input type="text" class="rcil-video-editor-input rcil-video-input" placeholder="' + (rcil_params.video_url_placeholder || 'YouTube URL...') + '" />' +
                    '<input type="text" class="rcil-video-editor-input rcil-zoom-input" placeholder="' + (rcil_params.zoom_url_placeholder || 'Zoom Call URL...') + '" style="margin-top:5px;" />' +
                    '<div class="rcil-video-editor-available-container" style="margin-top:5px; display:flex; align-items:center; gap:8px;">' +
                        '<span class="dashicons dashicons-calendar-alt" style="color:#666;"></span>' +
                        '<input type="datetime-local" class="rcil-video-editor-input rcil-available-input" style="flex:1;" />' +
                    '</div>' +
                    '<div class="rcil-video-editor-actions"> ' +
                        '<div class="rcil-video-editor-actions-left">' +
                            '<button type="button" class="rcil-video-editor-save button button-small">Guardar</button>' +
                            '<button type="button" class="rcil-video-editor-cancel button button-small">Cancelar</button>' +
                        '</div>' +
                        '<button type="button" class="rcil-video-editor-delete" aria-label="Delete lesson" title="Delete lesson">' +
                            '<span class="dashicons dashicons-trash"></span>' +
                        '</button>' +
                    '</div>' +
                    '<span class="rcil-video-editor-status" aria-live="polite"></span>' +
                '</div>'
            );
            $editor.data('initial-video', value);
            $editor.data('initial-zoom', zoomValue);
            $editor.data('initial-available', availableValue);
            $editor.data('initial-title', title);
            $editor.find('.rcil-video-input').val(value);
            $editor.find('.rcil-zoom-input').val(zoomValue);
            $editor.find('.rcil-available-input').val(availableValue);

            if ($a && $a.length) {
                $editor.css({
                    paddingLeft: $a.css('padding-left'),
                    paddingRight: $a.css('padding-right'),
                });
            }

            if ($a && $a.length) {
                $a.after($editor);
            } else {
                if ($preview.is('.rcp-lesson-card')) {
                    const $left = $preview.children('.flex-1').first();
                    const $titleRow = $left.find('div.flex.items-center.space-x-2').first();
                    if ($titleRow.length) {
                        $titleRow.after($editor);
                    } else if ($left.length) {
                        $left.append($editor);
                    } else {
                        $preview.append($editor);
                    }
                    return $editor;
                }

                const $details = $preview.children('.ld-item-details');
                if ($details.length) {
                    $details.first().before($editor);
                } else {
                    $preview.append($editor);
                }
            }
            return $editor;
        }

        function rcilEnsureTitleTextWrap($a, entry) {
            const $titleSpan = $a.find('.ld-item-title span').first();
            if (!$titleSpan.length) return null;

            let $text = $titleSpan.find('.rcil-lesson-title-text').first();
            if (!$text.length) {
                $text = $('<span class="rcil-lesson-title-text"></span>');
                const $lock = $titleSpan.find('.rcil-custom-lock-ico').first();
                if ($lock.length) {
                    // Move everything after the lock into the editable text span.
                    let node = $lock.get(0).nextSibling;
                    while (node) {
                        const next = node.nextSibling;
                        $text.append(node);
                        node = next;
                    }
                    $lock.after($text);
                } else {
                    $text.append($titleSpan.contents());
                    $titleSpan.append($text);
                }
            }

            if (entry && typeof entry.title === 'string') {
                $text.text(entry.title);
            }
            return $text;
        }

        function rcilSetCaretFromPoint(el, x, y) {
            try {
                if (document.caretPositionFromPoint) {
                    const pos = document.caretPositionFromPoint(x, y);
                    if (pos) {
                        const range = document.createRange();
                        range.setStart(pos.offsetNode, pos.offset);
                        range.collapse(true);
                        const sel = window.getSelection();
                        sel.removeAllRanges();
                        sel.addRange(range);
                        return true;
                    }
                }
                if (document.caretRangeFromPoint) {
                    const range = document.caretRangeFromPoint(x, y);
                    if (range) {
                        const sel = window.getSelection();
                        sel.removeAllRanges();
                        sel.addRange(range);
                        return true;
                    }
                }
            } catch (e) {}
            return false;
        }

        if (rcil_params && rcil_params.is_admin && rcil_params.lesson_video_map) {
            // Inject a small "Edit" trigger next to each lesson title for admins.
            function rcilInjectEditTriggers() {
                $.each(rcil_params.lesson_video_map, function (url, entry) {
                    if (!entry || !entry.id) return;
                    const $a = $('a[href="' + url + '"], a[href="' + url + '/"]').first();
                    if (!$a.length) return;

                    const $titleSpan = $a.find('.ld-item-title span').first();
                    if (!$titleSpan.length) return;

                    const $titleText = rcilEnsureTitleTextWrap($a, entry);
                    if (!$titleText || !$titleText.length) return;

                    if ($titleSpan.find('.rcil-lesson-edit-trigger').length) return;

                    const $btn = $('<button type="button" class="rcil-lesson-edit-trigger">Editar</button>');
                    $btn.attr('data-lesson-id', entry.id);
                    $titleText.after($btn);
                });
            }

            rcilInjectEditTriggers();

            // Open editor + enable inline title edit when clicking the "Edit" button.
            $(document).on('click', '.rcil-lesson-edit-trigger', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (typeof e.stopImmediatePropagation === 'function') {
                    e.stopImmediatePropagation();
                }

                const $btn = $(this);
                const $rcpCard = $btn.closest('.rcp-lesson-card');
                if ($rcpCard.length) {
                    const href = $btn.attr('data-lesson-url') || $rcpCard.attr('data-rcp-href') || '';
                    const entry = rcilGetLessonEntryFromHref(href);
                    if (!entry || !entry.id) return;

                    let $editor = $rcpCard.find('.rcil-video-editor').first();
                    if (!$editor.length) {
                        $editor = rcilEnsureEditor($rcpCard, entry, null);
                    }
                    $editor.addClass('is-open');
                    $editor.data('context', 'rcp');
                    $editor.data('lesson-href', href);
                    $rcpCard.addClass('rcil-editing');
                    return;
                }

                const $a = $btn.closest('a');
                if (!$a.length) return;

                const entry = rcilGetLessonEntryFromHref($a.attr('href'));
                if (!entry || !entry.id) return;

                const $preview = $a.closest('.ld-item-list-item-preview');
                const $editor = rcilEnsureEditor($preview, entry, $a);
                $editor.addClass('is-open');
                $preview.addClass('rcil-editing');

                const $titleText = rcilEnsureTitleTextWrap($a, entry);
                if ($titleText && $titleText.length) {
                    $titleText.attr('contenteditable', 'true').addClass('is-editing').trigger('focus');
                    window.setTimeout(function () {
                        const range = document.createRange();
                        range.selectNodeContents($titleText.get(0));
                        range.collapse(false);
                        const sel = window.getSelection();
                        sel.removeAllRanges();
                        sel.addRange(range);
                    }, 0);
                }
            });

            // Prevent navigation when interacting with the editor UI.
            $(document).on('click', '.rcil-video-editor, .rcil-video-editor *', function (e) {
                e.stopPropagation();
            });

            $(document).on('click', '.rcil-video-editor-cancel', function (e) {
                e.preventDefault();
                const $editor = $(this).closest('.rcil-video-editor');
                const $rcpCard = $editor.closest('.rcp-lesson-card');
                const $preview = $editor.closest('.ld-item-list-item-preview');
                const initialVideo = $editor.data('initial-video') || '';
                const initialZoom = $editor.data('initial-zoom') || '';
                const initialAvailable = $editor.data('initial-available') || '';
                const initialTitle = $editor.data('initial-title') || '';
                $editor.find('.rcil-video-input').val(initialVideo);
                $editor.find('.rcil-zoom-input').val(initialZoom);
                $editor.find('.rcil-available-input').val(initialAvailable);
                if ($rcpCard.length) {
                    const $title = $rcpCard.find('.rcil-rcp-lesson-title').first();
                    if ($title.length) {
                        $title.text(initialTitle).removeAttr('contenteditable');
                    }
                    $editor.removeClass('is-open');
                    $rcpCard.removeClass('rcil-editing');
                    return;
                }
                const $a = $editor.closest('.ld-item-list-item-preview').find('a').first();
                const $titleText = rcilEnsureTitleTextWrap($a, { title: initialTitle });
                if ($titleText && $titleText.length) {
                    $titleText.text(initialTitle).removeAttr('contenteditable').removeClass('is-editing');
                }
                $editor.removeClass('is-open');
                $preview.removeClass('rcil-editing');
            });

            // RCP course template: enable title edit on click while in edit mode.
            $(document).on('click', '.rcp-lesson-card.rcil-editing .rcil-rcp-lesson-title', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (typeof e.stopImmediatePropagation === 'function') {
                    e.stopImmediatePropagation();
                }
                const $title = $(this);
                if ($title.attr('contenteditable') === 'true') return;
                $title.attr('contenteditable', 'true').trigger('focus');
                window.setTimeout(function () {
                    try {
                        const el = $title.get(0);
                        const range = document.createRange();
                        range.selectNodeContents(el);
                        range.collapse(false);
                        const sel = window.getSelection();
                        sel.removeAllRanges();
                        sel.addRange(range);
                    } catch (err) {}
                }, 0);
            });

            $(document).on('keydown', '.rcil-video-editor-input, .rcil-lesson-title-text[contenteditable=\"true\"]', function (e) {
                if (e.key === 'Escape') {
                    const $editor = $(this).closest('.rcil-video-editor');
                    const $rcpCard = $editor.closest('.rcp-lesson-card');
                    const $preview = $editor.closest('.ld-item-list-item-preview');
                    const initialVideo = $editor.data('initial-video') || '';
                    const initialZoom = $editor.data('initial-zoom') || '';
                    const initialAvailable = $editor.data('initial-available') || '';
                    const initialTitle = $editor.data('initial-title') || '';
                    $editor.find('.rcil-video-input').val(initialVideo);
                    $editor.find('.rcil-zoom-input').val(initialZoom);
                    $editor.find('.rcil-available-input').val(initialAvailable);
                    if ($rcpCard.length) {
                        const $title = $rcpCard.find('.rcil-rcp-lesson-title').first();
                        if ($title.length) {
                            $title.text(initialTitle).removeAttr('contenteditable');
                        }
                        $editor.removeClass('is-open');
                        $rcpCard.removeClass('rcil-editing');
                        return;
                    }
                    const $a = $editor.closest('.ld-item-list-item-preview').find('a').first();
                    const $titleText = rcilEnsureTitleTextWrap($a, { title: initialTitle });
                    if ($titleText && $titleText.length) {
                        $titleText.text(initialTitle).removeAttr('contenteditable').removeClass('is-editing');
                    }
                    $editor.removeClass('is-open');
                    $preview.removeClass('rcil-editing');
                } else if (e.key === 'Enter' && $(this).is('.rcil-lesson-title-text[contenteditable=\"true\"]')) {
                    e.preventDefault(); // don't insert newline
                    $(this).closest('.ld-item-list-item-preview').find('.rcil-video-editor-save').first().trigger('click');
                }
            });

            $(document).on('click', '.rcil-video-editor-save', function (e) {
                e.preventDefault();
                const $btn = $(this);
                const $editor = $btn.closest('.rcil-video-editor');
                const $status = $editor.find('.rcil-video-editor-status');
                const $rcpCard = $editor.closest('.rcp-lesson-card');

                const lessonId = parseInt($editor.data('lesson-id'), 10) || 0;
                const videoUrl = $editor.find('.rcil-video-input').val() || '';
                const zoomUrl = $editor.find('.rcil-zoom-input').val() || '';
                const availableFrom = $editor.find('.rcil-available-input').val() || '';
                let $a = $();
                let title = '';
                let href = '';
                if ($rcpCard.length) {
                    href = $editor.data('lesson-href') || $rcpCard.attr('data-rcp-href') || '';
                    title = ($rcpCard.find('.rcil-rcp-lesson-title').first().text() || '').trim();
                } else {
                    $a = $editor.closest('.ld-item-list-item-preview').find('a').first();
                    const $titleText = rcilEnsureTitleTextWrap($a, null);
                    title = ($titleText && $titleText.length) ? ($titleText.text() || '').trim() : '';
                    href = $a.attr('href') || '';
                }
                if (!lessonId) return;
                if (!title) return;

                $btn.prop('disabled', true);
                $status.removeClass('is-error is-success').text(rcil_params.saving_label || 'Saving...');

                $.ajax({
                    type: 'POST',
                    url: rcil_params.ajax_url,
                    data: {
                        action: 'rcil_update_lesson_details',
                        nonce: rcil_params.nonce,
                        lesson_id: lessonId,
                        title: title,
                        video_url: videoUrl,
                        zoom_url: zoomUrl,
                        available_from: availableFrom
                    },
                    success: function (response) {
                        if (response && response.success && response.data) {
                            $status.addClass('is-success').text(rcil_params.saved_label || 'Saved');
                            $editor.data('initial-video', response.data.video_url || '');
                            $editor.data('initial-zoom', response.data.zoom_url || '');
                            $editor.data('initial-available', response.data.available_from_iso || '');
                            $editor.data('initial-title', response.data.title || title);
 
                            // Update local map so re-open shows latest value.
                            const entry = rcilGetLessonEntryFromHref(href);
                            if (entry) {
                                entry.video_url = response.data.video_url || '';
                                entry.zoom_url = response.data.zoom_url || '';
                                entry.available_from_iso = response.data.available_from_iso || '';
                                entry.title = response.data.title || title;
                            }

                            if (!$rcpCard.length) {
                                // Update displayed title in list.
                                const $text = rcilEnsureTitleTextWrap($a, entry || { title: title });
                                if ($text && $text.length) {
                                    $text.text((entry && entry.title) ? entry.title : title).removeAttr('contenteditable').removeClass('is-editing');
                                }
                                $editor.closest('.ld-item-list-item-preview').removeClass('rcil-editing');
                            }
                            $editor.removeClass('is-open');
                            if ($rcpCard.length) {
                                const $title = $rcpCard.find('.rcil-rcp-lesson-title').first();
                                if ($title.length) {
                                    $title.text(response.data.title || title).removeAttr('contenteditable');
                                }
                                $rcpCard.removeClass('rcil-editing');
                            }
                        } else {
                            $status.addClass('is-error').text((response && response.data) ? response.data : (rcil_params.server_error_label || 'Error'));
                        }
                    },
                    error: function () {
                        $status.addClass('is-error').text(rcil_params.server_error_label || 'Error');
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                        window.setTimeout(function () { $status.text(''); }, 2500);
                    }
                });
            });

            $(document).on('click', '.rcil-video-editor-delete', function (e) {
                e.preventDefault();
                const $btn = $(this);
                const $editor = $btn.closest('.rcil-video-editor');
                const $status = $editor.find('.rcil-video-editor-status');
                const $rcpCard = $editor.closest('.rcp-lesson-card');

                const lessonId = parseInt($editor.data('lesson-id'), 10) || 0;
                if (!lessonId) return;

                if (!window.confirm(rcil_params.delete_confirm_label || 'Are you sure you want to delete this lesson?')) {
                    return;
                }

                $btn.prop('disabled', true);
                $status.removeClass('is-error is-success').text(rcil_params.deleting_label || 'Deleting...');

                $.ajax({
                    type: 'POST',
                    url: rcil_params.ajax_url,
                    data: {
                        action: 'rcil_delete_lesson',
                        nonce: rcil_params.nonce,
                        lesson_id: lessonId
                    },
                    success: function (response) {
                        if (response && response.success) {
                            if ($rcpCard.length) {
                                $rcpCard.remove();
                            } else {
                                const $preview = $editor.closest('.ld-item-list-item-preview');
                                const $row = $preview.closest('li, .ld-item-list-item');
                                ($row.length ? $row : $preview).remove();
                            }
                        } else {
                            $status.addClass('is-error').text((response && response.data) ? response.data : (rcil_params.server_error_label || 'Error'));
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function () {
                        $status.addClass('is-error').text(rcil_params.server_error_label || 'Error');
                        $btn.prop('disabled', false);
                    }
                });
            });
        }
    });
})(jQuery);
