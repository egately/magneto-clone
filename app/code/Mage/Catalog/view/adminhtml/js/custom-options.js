/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
(function ($) {
    $.widget('mage.customOptions', {
        options: {
            selectionItemCount: {}
        },
        _create: function () {
            this._initOptionBoxes();
            this._initSortableSelections();
            this._bindCheckboxHandlers();
            this._bindReadOnlyMode();
        },
        _initOptionBoxes: function () {
            if (!this.options.isReadonly) {
                this.element.sortable({
                    axis: 'y',
                    handle: '[data-role="grip"]',
                    items: '#product_options_container_top > div',
                    update: this._updateOptionBoxPositions,
                    tolerance: 'pointer'
                });
            }
            var syncOptionTitle = function (event) {
                var originalValue = $(event.target).attr('data-store-label'),
                    currentValue = $(event.target).val(),
                    optionBoxTitle = $('.title > span', $(event.target).closest('.fieldset-wrapper')),
                    newOptionTitle = $.mage.__('New Option');

                optionBoxTitle.text(currentValue === '' && originalValue.length ? newOptionTitle : currentValue);
            };
            this._on({
                //reset field value to Default
                'click .use-default-label': function (event) {
                    $(event.target).closest('label').find('input').prop('checked', true).trigger('change');
                },
                //Remove custom option or option row for 'select' type of custom option
                'click button[id^=product_option_][id$=_delete]': function (event) {
                    var element = $(event.target).closest('#product_options_container_top > div.fieldset-wrapper,tr');
                    if (element.length) {
                        $('#product_' + element.attr('id').replace('product_', '') + '_is_delete').val(1);
                        element.addClass('ignore-validate').hide();
                        this.refreshSortableElements();
                    }
                },
                //Minimize custom option block
                'click #product_options_container_top [data-target$=-content]': function (event) {
                    if (this.options.isReadonly) {
                        return false;
                    }
                },
                //Add new custom option
                'click #add_new_defined_option': function (event) {
                    this.addOption(event);
                },
                //Add new option row for 'select' type of custom option
                'click button[id^=product_option_][id$=_add_select_row]': function (event) {
                    this.addSelection(event);
                },
                //Import custom options from products
                'click #import_new_defined_option': function () {
                    var importContainer = $('#import-container'),
                        widget = this;

                    importContainer.dialog({
                        title: $.mage.__('Select Product'),
                        autoOpen: false,
                        minWidth: 980,
                        modal: true,
                        resizable: true,
                        buttons: [
                            {
                                text: $.mage.__('Close'),
                                id: 'import-custom-options-close-button',
                                click: function () {
                                    $(this).dialog('close');
                                }
                            },
                            {
                                text: $.mage.__('Import'),
                                id: 'import-custom-options-apply-button',
                                click: function (event, massActionTrigger) {
                                    var request = [];
                                    $(this).find('input[name=product]:checked').map(function () {
                                        request.push(this.value);
                                    });

                                    if (request.length === 0) {
                                        if (!massActionTrigger) {
                                            alert($.mage.__('Please select items.'));
                                        }
                                        return;
                                    }

                                    $.post(widget.options.customOptionsUrl, {
                                        'products[]': request,
                                        form_key: widget.options.formKey
                                    }, function ($data) {
                                        $.parseJSON($data).each(function (el) {
                                            el.id = widget.getFreeOptionId(el.id);
                                            el.option_id = el.id;
                                            if (typeof el.optionValues !== 'undefined') {
                                                for (var i = 0; i < el.optionValues.length; i++) {
                                                    el.optionValues[i].option_id = el.id;
                                                }
                                            }
                                            //Adding option
                                            widget.addOption(el);
                                            //Will save new option on server side
                                            $('#product_option_' + el.id + '_option_id').val(0);
                                            $('#option_' + el.id + ' input[name$="option_type_id]"]').val(-1);
                                        });
                                        importContainer.dialog('close');
                                    });
                                }
                            }
                        ]
                    });
                    importContainer.load(
                        this.options.productGridUrl,
                        {form_key: this.options.formKey},
                        function () {
                            importContainer.dialog('open');
                        }
                    );
                },
                'click #productGrid_massaction-form button': function () {
                    $('#import-custom-options-apply-button').trigger('click', 'massActionTrigger');
                },
                //Change custom option type
                'change select[id^=product_option_][id$=_type]': function (event, data) {
                    data = data || {};
                    var widget = this,
                        currentElement = $(event.target),
                        parentId = '#' + currentElement.closest('.fieldset-alt').attr('id'),
                        group = currentElement.find('[value="' + currentElement.val() + '"]').closest('optgroup').attr('label'),
                        previousGroup = $(parentId + '_previous_group').val(),
                        previousBlock = $(parentId + '_type_' + previousGroup);
                    if (typeof group !== 'undefined') {
                        group = group.toLowerCase();
                    }
                    if (previousGroup !== group) {
                        if (previousBlock.length) {
                            previousBlock.addClass('ignore-validate').hide();
                        }
                        $(parentId + '_previous_group').val(group);
                        if (typeof group === 'undefined') {
                            return;
                        }
                        var disabledBlock = $(parentId).find(parentId + '_type_' + group);
                        if (disabledBlock.length) {
                            disabledBlock.removeClass('ignore-validate').show();
                        } else {
                            if ($.isEmptyObject(data)) {
                                data.option_id = $(parentId + '_id').val();
                                data.price = data.sku = '';
                            }
                            data.group = group;
                            widget.element.find('#custom-option-' + group + '-type-template').tmpl(data).insertAfter($(parentId));
                            if (data.price_type) {
                                var priceType = $('#' + widget.options.fieldId + '_' + data.option_id + '_price_type');
                                priceType.val(data.price_type).attr('data-store-label', data.price_type);
                            }
                            this._bindUseDefault(widget.options.fieldId + '_' + data.option_id, data);
                            //Add selections
                            if (data.optionValues) {
                                data.optionValues.each(function (value) {
                                    widget.addSelection(value);
                                });
                            }
                        }
                    }
                },
                //Sync title
                'change .field-option-title > .control > input[id$="_title"]': syncOptionTitle,
                'keyup .field-option-title > .control > input[id$="_title"]': syncOptionTitle,
                'paste .field-option-title > .control > input[id$="_title"]': syncOptionTitle
            });
        },
        _initSortableSelections: function () {
            if (!this.options.isReadonly) {
                this.element.find('[id^=product_option_][id$=_type_select] tbody').sortable({
                    axis: 'y',
                    handle: '[data-role="grip"]',
                    helper: function (event, ui) {
                        ui.children().each(function () {
                            $(this).width($(this).width());
                        });
                        return ui;
                    },
                    update:this._updateSelectionsPositions,
                    tolerance: 'pointer'
                });
            }
        },
        //Sync sort order checkbox with hidden dropdown
        _bindCheckboxHandlers: function () {
            this._on({
                'change [id^=product_option_][id$=_required]': function (event) {
                    var $this = $(event.target);
                    $this.closest('#product_options_container_top > div').find('[name$="[is_require]"]').val($this.is(':checked') ? 1 : 0);
                }
            });
            this.element.find('[id^=product_option_][id$=_required]').each(function () {
                $(this).prop('checked', $(this).closest('#product_options_container_top > div').find('[name$="[is_require]"]').val() > 0);
            });
        },
        //Update Custom option position
        _updateOptionBoxPositions: function () {
            $(this).find('div[id^=option_]:not(.ignore-validate) .fieldset-alt > [name$="[sort_order]"]').each(function (index) {
                $(this).val(index);
            });
        },
        //Update selections positions for 'select' type of custom option
        _updateSelectionsPositions: function () {
            $(this).find('tr:not(.ignore-validate) [name$="[sort_order]"]').each(function (index) {
                $(this).val(index);
            });
        },
        //Disable input data if "Read Only"
        _bindReadOnlyMode: function () {
            if (this.options.isReadonly) {
                $('div.product-custom-options').find('button,input,select,textarea,').each(function () {
                    $(this).prop('disabled', true);
                    if ($(this).is('button')) {
                        $(this).addClass('disabled');
                    }
                });
            }
        },
        _bindUseDefault: function (id, data) {
            var title = $('#' + id + '_title'),
                price = $('#' + id + '_price'),
                priceType = $('#' + id + '_price_type');
            //enable 'use default' link for title
            if (data.checkboxScopeTitle) {
                title.useDefault({
                    field:'.field',
                    useDefault:'label[for$=_title]',
                    checkbox:'input[id$=_title_use_default]',
                    label:'span'
                });
            }
            //enable 'use default' link for price and price_type
            if (data.checkboxScopePrice) {
                price.useDefault({
                    field:'.field',
                    useDefault:'label[for$=_price]',
                    checkbox:'input[id$=_price_use_default]',
                    label:'span'
                });
                //@TODO not work set default value for second field
                priceType.useDefault({
                    field:'.field',
                    useDefault:'label[for$=_price]',
                    checkbox:'input[id$=_price_use_default]',
                    label:'span'
                });
            }
        },
        //Add selection value for 'select' type of custom option
        addSelection: function (event) {
            var data = {},
                element = event.target || event.srcElement || event.currentTarget;
            if (typeof element !== 'undefined') {
                data.id = $(element).closest('#product_options_container_top > div')
                    .find('[name^="product[options]"][name$="[id]"]').val();
                data.option_type_id = -1;
                if (!this.options.selectionItemCount[data.id]) {
                    this.options.selectionItemCount[data.id] = 1;
                }
                data.select_id = this.options.selectionItemCount[data.id];
                data.price = data.sku = '';
            } else {
                data = event;
                data.id = data.option_id;
                data.select_id = data.option_type_id;
                this.options.selectionItemCount[data.id] = data.item_count;
            }
            this.element.find('#custom-option-select-type-row-template').tmpl(data)
                .appendTo($('#select_option_type_row_' + data.id));

            //set selected price_type value if set
            if (data.price_type) {
                var priceType = $('#' + this.options.fieldId + '_' + data.id + '_select_' + data.select_id + '_price_type');
                priceType.val(data.price_type).attr('data-store-label', data.price_type);
            }
            this._bindUseDefault(this.options.fieldId + '_' + data.id + '_select_' + data.select_id, data);
            this.refreshSortableElements();
            this.options.selectionItemCount[data.id] = parseInt(this.options.selectionItemCount[data.id], 10) + 1;
        },
        //Add custom option
        addOption: function (event) {
            var data = {},
                element = event.target || event.srcElement || event.currentTarget;
            if (typeof element !== 'undefined') {
                data.id = this.options.itemCount;
                data.type = '';
                data.option_id = 0;
                data.title = $.mage.__('New Option');
            } else {
                data = event;
                this.options.itemCount = data.item_count;
            }
            this.element.find('#custom-option-base-template').tmpl(data)
                .appendTo(this.element.find('#product_options_container_top'));
            //set selected type value if set
            if (data.type) {
                $('#' + this.options.fieldId + '_' + data.id + '_type').val(data.type).trigger('change', data);
            }
            //set selected is_require value if set
            if (data.is_require) {
                $('#' + this.options.fieldId + '_' + data.id + '_is_require').val(data.is_require).trigger('change');
            }
            $('.collapse').collapsable();
            this.refreshSortableElements();
            this._bindCheckboxHandlers();
            this._bindReadOnlyMode();
            this.options.itemCount++;
        },
        refreshSortableElements: function () {
            if (!this.options.isReadonly) {
                this.element.sortable('refresh');
                this._updateOptionBoxPositions.apply(this.element);
                this._updateSelectionsPositions.apply(this.element);
                this._initSortableSelections();
            }
            return this;
        },
        getFreeOptionId: function (id) {
            return $('#' + this.options.fieldId + '_' + id).length ? this.getFreeOptionId(parseInt(id, 10) + 1) : id;
        }
    });
})(jQuery);
