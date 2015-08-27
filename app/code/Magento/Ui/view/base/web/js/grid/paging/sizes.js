/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore',
    'Magento_Ui/js/lib/collapsible'
], function (ko, _, Collapsible) {
    'use strict';

    /**
     * Returns closest existing page number to page argument
     * @param {Number} value
     * @param {Number} min
     * @param {Number} max
     * @returns {Number} closest existing page number
     */
    function getInRange(value, min, max) {
        return Math.min(Math.max(min, value), max);
    }

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/paging/sizes',
            value: 20,
            minSize: 1,
            maxSize: 1000,
            customVisible: false,
            customValue: '',
            options: [],
            links: {
                value: '${ $.storageConfig.path }.value',
                options: '${ $.storageConfig.path }.options'
            },
            listens: {
                value: 'onValueChange',
                options: 'onSizesChange'
            }
        },

        /**
         *
         * @returns {Sizes} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('options editing value customVisible customValue');

            this._value = ko.pureComputed({
                read: this.value,

                /**
                 * Validates input field prior to updating 'value' property.
                 */
                write: function (value) {
                    value = this.normalize(value);

                    this.value(value);
                    this._value.notifySubscribers(value);
                },

                owner: this
            });

            return this;
        },

        /**
         * Starts editing of the specified size.
         *
         * @param {Number} value - Value of the size.
         * @returns {Sizes} Chainable.
         */
        edit: function (value) {
            this.editing(value);

            return this;
        },

        /**
         * Discards changes made to the currently editable size.
         *
         * @returns {Sizes} Chainable.
         */
        discardEditing: function () {
            var value = this.editing();

            if (value) {
                this.updateSize(value, value);
            }

            return this;
        },

        /**
         * Invokes 'discardEditing' and 'discardCustom' actions.
         *
         * @returns {Sizes} Chainable.
         */
        discardAll: function () {
            this.discardEditing()
                .discardCustom();

            return this;
        },

        /**
         * Returns value of the first size.
         *
         * @returns {Number}
         */
        getFirst: function () {
            return this.options()[0].value;
        },

        /**
         * Returns size which matches specified value.
         *
         * @param {Number} value - Value of the item in sizes array.
         * @returns {Object|Undefined}
         */
        getSize: function (value) {
            return _.findWhere(this.options(), {
                value: value
            });
        },

        /**
         *
         * @returns {Sizes} Chainable.
         */
        setSize: function (value) {
            this.value(value);

            return this;
        },

        /**
         * Adds a new value to sizes list.
         *
         * @param {Number} value - Value to be added.
         * @returns {Sizes} Chainable.
         */
        addSize: function (value) {
            var items = this.options();

            if (this.hasSize(value)) {
                return this;
            }

            items.push(this.createSize(value));

            this.options(this.sort(items));

            return this;
        },

        /**
         * Removes provided value from the sizes list.
         *
         * @param {Number} value - Value to be removed.
         * @returns {Sizes} Chainable.
         */
        removeSize: function (value) {
            var size = this.getSize(value);

            if (!size) {
                return this;
            }

            this.options.remove(size);

            if (this.isSelected(value)) {
                this.setSize(this.getFirst());
            }

            return this;
        },

        /**
         * Updates existing value to the provided one.
         *
         *
         * @param {Number} value
         * @param {(Number|String)} [newValue=size._value]
         * @returns {Sizes} Chainable.
         */
        updateSize: function (value, newValue) {
            var size = this.getSize(value);

            if (!size) {
                return this;
            }

            newValue = newValue || size._value;
            newValue = this.normalize(newValue);

            this.options.remove(size);
            this.addSize(newValue);

            if (this.isSelected(value)) {
                this.setSize(newValue);
            }

            return this;
        },

        /**
         * Creates new editable size instance with the provided value.
         *
         * @param {Number} value - Value of the size.
         * @returns {Object}
         */
        createSize: function (value) {
            return {
                value: value,
                label: value,
                _value: value,
                editable: true
            };
        },

        /**
         * Chechks if provided value exists in the sizes list.
         *
         * @returns {Boolean}
         */
        hasSize: function (value) {
            return !!this.getSize(value);
        },

        /**
         * Hides and empties custom field.
         *
         * @returns {Sizes} Chainable.
         */
        discardCustom: function () {
            this.hideCustom()
                .clearCustom();

            return this;
        },

        /**
         * Shows custom field.
         *
         * @returns {Sizes} Chainable.
         */
        showCustom: function () {
            this.customVisible(true);

            return this;
        },

        /**
         * Hides custom field.
         *
         * @returns {Sizes} Chainable.
         */
        hideCustom: function () {
            this.customVisible(false);

            return this;
        },

        /**
         * Empties value of the custom field.
         *
         * @returns {Sizes} Chainable.
         */
        clearCustom: function () {
            this.customValue('');

            return this;
        },

        /**
         * Adds a new size specified in the custom field.
         *
         * @returns {Sizes} Chainable.
         */
        applyCustom: function () {
            var value = this.customValue();

            value = this.normalize(value);

            this.addSize(value)
                .setSize(value)
                .discardCustom();

            return this;
        },

        /**
         * Checks if custom field is visible.
         *
         * @returns {Boolean}
         */
        isCustomVisible: function () {
            return this.customVisible();
        },

        /**
         * Converts provided value to a number and puts
         * it in range between 'minSize' and 'maxSize' properties.
         *
         * @param {(Number|String)} value - Value to be normalized.
         * @returns {Number}
         */
        normalize: function (value) {
            value = +value;

            if (isNaN(value)) {
                return this.getFirst();
            }

            return getInRange(Math.round(value), this.minSize, this.maxSize);
        },

        /**
         * Sorts provided array in ascending order by
         * the 'value' property of its' items.
         *
         * @param {Array} [data=this.options] - Array to be sorted.
         * @returns {Array} Sorted array.
         */
        sort: function (data) {
            data = data || this.options();

            return _.sortBy(data, 'value');
        },

        /**
         * Checks if provided value is in editing state.
         *
         * @returns {Boolean}
         */
        isEditing: function (value) {
            return this.editing() === value;
        },

        /**
         *
         * @returns {Boolean}
         */
        isSelected: function (value) {
            return this.value() === value;
        },

        /**
         * Listener of the 'value' property changes.
         */
        onValueChange: function () {
            this.close()
                .discardAll();
        },

        /**
         * Listener of the 'options' array changes.
         */
        onSizesChange: function () {
            this.editing(false);
        }
    });
});
