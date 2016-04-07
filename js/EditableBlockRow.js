(function ($) {
    $.entwine("ss", function ($) {

        // A global method on grid fields to display the no items message if no rows are found
        $(".ss-gridfield").entwine({
            showNoItemsMessage: function () {
                if (this.find('.ss-gridfield-items:first').children().not('.ss-gridfield-no-items').length === 0) {
                    this.find('.ss-gridfield-no-items').show();
                }
            }
        });

        // Milkyway\SS\GridFieldUtils\EditableRow
        $('.cms-container').entwine({
            OpenGridFieldToggles: {},
            saveTabState: function () {
                var $that = this,
                    OpenGridFieldToggles = $that.getOpenGridFieldToggles();

                $that._super();

                $that.find('.ss-gridfield.ss-gridfield-editable-rows').each(function () {
                    var $this = $(this),
                        openToggles = $this.getOpenToggles();

                    if (openToggles.length) {
                        OpenGridFieldToggles[$this.attr('id')] = $this.getOpenToggles();
                    }
                });
            },
            restoreTabState: function (overrideStates) {
                var $that = this,
                    OpenGridFieldToggles = $that.getOpenGridFieldToggles();

                $that._super(overrideStates);

                $.each(OpenGridFieldToggles, function (id, openToggles) {
                    $that.find('#' + id + '.ss-gridfield.ss-gridfield-editable-rows').reopenToggles(openToggles);
                });

                $that.find('.ss-gridfield-editable-row--toggle_start').click();

                $that.setOpenGridFieldToggles({});
            }
        });

        $(".ss-gridfield.ss-gridfield-editable-rows").entwine({
            reload: function (opts, success) {
                var $grid = this,
                    openToggles = $grid.getOpenToggles(),
                    args = arguments;

                this._super(opts, function () {
                    $grid.reopenToggles(openToggles);
                    $grid.find('.ss-gridfield-editable-row--toggle_start').click();

                    if (success) {
                        success.apply($grid, args);
                    }
                });
            },
            getOpenToggles: function () {
                var $grid = this,
                    openToggles = [];

                if ($grid.hasClass('ss-gridfield-editable-rows_disableToggleState')) {
                    return openToggles;
                }

                $grid.find(".ss-gridfield-editable-row--toggle_open").each(function (key) {
                    var $this = $(this),
                        $holder = $this.parents('td:first'),
                        $parent = $this.parents('tr:first'),
                        $currentGrid = $parent.parents('.ss-gridfield:first'),
                        $editable = $parent.next();

                    if (!$editable.hasClass('ss-gridfield-editable-row--row') 
                            || $editable.data('id') != $parent.data('id') 
                            || $editable.data('class') != $parent.data('class')) {
                        $editable = null;
                    }
                    else if ($currentGrid.hasClass('ss-gridfield-editable-rows_disableToggleState')) {
                        return true;
                    }

                    openToggles[key] = {
                        link: $holder.data('link')
                    };

                    if ($editable) {
                        $editable.find('.ss-tabset.ui-tabs').each(function () {
                            if (!openToggles[key].tabs) {
                                openToggles[key].tabs = {};
                            }

                            openToggles[key].tabs[this.id] = $(this).tabs('option', 'selected');
                        });

                        if ($currentGrid.hasClass('ss-gridfield-editable-rows_allowCachedToggles')) {
                            openToggles[key].row = $editable.detach();
                        }
                    }
                });

                return openToggles;
            },
            reopenToggles: function (openToggles) {
                var $grid = this,
                    openTabsInToggle = function (currentToggle, $row) {
                        if (currentToggle.hasOwnProperty('tabs') && currentToggle.tabs) {
                            $.each(currentToggle.tabs, function (key, value) {
                                $row.find('#' + key + '.ss-tabset.ui-tabs').tabs({
                                    active: value
                                });
                            });
                        }
                    };

                if ($grid.hasClass('ss-gridfield-editable-rows_disableToggleState')) {
                    return;
                }

                $.each(openToggles, function (key) {
                    if (openToggles[key].hasOwnProperty('link') && openToggles[key].link) {
                        var $toggleHolder = $grid.find("td.ss-gridfield-editable-row--icon-holder[data-link='" 
                                + openToggles[key].link + "']");

                        if (!$toggleHolder.length) {
                            return true;
                        }
                    }
                    else {
                        return true;
                    }

                    if (openToggles[key].hasOwnProperty('row') && openToggles[key].row) {
                        var $parent = $toggleHolder.parents('tr:first');

                        $toggleHolder
                            .find(".ss-gridfield-editable-row--toggle")
                            .addClass('ss-gridfield-editable-row--toggle_loaded ss-gridfield-editable-row--toggle_open');

                        if (!$parent.next().hasClass('ss-gridfield-editable-row--row')) {
                            $parent.after(openToggles[key].row);
                        }
                    }
                    else if (openToggles[key].hasOwnProperty('link') && openToggles[key].link) {
                        $toggleHolder.find(".ss-gridfield-editable-row--toggle").trigger('click', function ($newRow) {
                            $grid.find('.ss-gridfield.ss-gridfield-editable-rows').reopenToggles(openToggles);
                            openTabsInToggle(openToggles[key], $newRow);
                        }, false);
                    }
                });
            }
        });

        $(".ss-gridfield-editable-row--toggle").entwine({
            onclick: function (e, callback, noFocus) {
                var $this = this,
                    $holder = $this.parents('td:first'),
                    link = $holder.data('link'),
                    $parent = $this.parents('tr:first');

                $this.removeClass('ss-gridfield-editable-row--toggle_start');

                if ($parent.hasClass('ss-gridfield-editable-row--loading')) {
                    return false;
                }

                if (link && !$this.hasClass('ss-gridfield-editable-row--toggle_loaded')) {
                    $parent.addClass('ss-gridfield-editable-row--loading');

                    $.ajax({
                        url: link,
                        dataType: 'html',
                        success: function (data) {
                            var $data = $(data);
                            $this.addClass('ss-gridfield-editable-row--toggle_loaded ss-gridfield-editable-row--toggle_open');
                            $parent.addClass('ss-gridfield-editable-row--reference')
                                    .removeClass('ss-gridfield-editable-row--loading');
                            $parent.after($data);

                            $data.find('.ss-gridfield-editable-row--toggle_start').click();

                            if (noFocus !== false) {
                                $data.find("input:first").focus();
                            }

                            if (typeof callback === 'function') {
                                callback($data, $this, $parent);
                            }
                        },
                        error: function (e) {
                            alert(ss.i18n._t('GRIDFIELD.ERRORINTRANSACTION'));
                            $parent.removeClass('ss-gridfield-editable-row--loading');
                        }
                    });
                }
                else if (link) {
                    var $editable = $parent.next();

                    if ($editable.hasClass('ss-gridfield-editable-row--row') 
                            && $editable.data('id') == $parent.data('id') 
                            && $editable.data('class') == $parent.data('class')) {
                        $this.toggleClass('ss-gridfield-editable-row--toggle_open');

                        if ($this.hasClass('ss-gridfield-editable-row--toggle_open')) {
                            $editable.removeClass('ss-gridfield-editable-row--row_hide')
                                    .find("input:first").focus();
                        }
                        else {
                            $editable.addClass('ss-gridfield-editable-row--row_hide');
                        }
                    }
                }

                return false;
            }
        });
        
    });
})(jQuery);