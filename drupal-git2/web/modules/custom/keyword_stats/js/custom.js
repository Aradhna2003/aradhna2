(function($, Drupal) {
  Drupal.behaviors.myModuleBehavior = {
    attach: function(context, settings) {
      var $viewElm = $('.view').attr('class');
      if ($viewElm) {
        var $splitArr = $viewElm.split(' ');
        var $viewIdClass = $splitArr.filter(function(className) {
          return className.startsWith('view-id');
        })[0];

        var viewIdValue = $viewIdClass ? $viewIdClass.split('view-id-')[1] : '';
        var inputValues = [];
        $('button.keyword_stats_button').click(function() {

          var inputValue = $('.views-exposed-form .js-form-item input.form-control', context);
          if (inputValue) {
            inputValue.each(function() {
              if ($(this).val()) {
                inputValues.push($(this).val());
              }
            });
          }
          var selectInput = $('.views-exposed-form .js-form-item select option:selected', context)
          selectInput.each(function() {
            var selectAll = selectInput.val();
            if (selectAll !== "All") {
              inputValues.push($(this).text());
            }
          });

          var selectedRadio = $('.views-exposed-form .js-form-item input.form-radio:checked', context);
          if (selectedRadio.length) {
            var radioLabel = selectedRadio.next('label').text().trim();
            var radioAll = selectedRadio.val();
            if (radioAll !== "All") {
              inputValues.push(radioLabel);
            }
          }

          var selectedCheckbox = $('.views-exposed-form .js-form-item input.form-checkbox:checked');
          if (selectedCheckbox.length) {
            selectedCheckbox.each(function() {
              var checkboxLabel = $(this).next('label').text().trim();
              inputValues.push(checkboxLabel);
            });
          }

          $.ajax({
            type: 'GET',
            url: Drupal.url('/keyword-stats-dbtable'),
            data: {
              titles: inputValues,
              viewId: viewIdValue
            },
            contentType: "application/json",
            dataType: 'json',

          });
        });
      }

      $(window).on('load', function() {
        var queryString = window.location.search;
        var params = new URLSearchParams(queryString);
        var filterValue = params.get('filter');
        $('select.filter-options').val(filterValue);

        // Highlight the sorted column
        var order = params.get('order');
        var sort = params.get('sort');
        if (order && sort) {
          $('#keyword-stats-table thead th.sortable[data-sort-field="' + order + '"]').addClass('sorted-' + sort);
        }
      });

      $(".filter-btn").click(function(e) {
        e.preventDefault();
        let selectedFilter = '';
        if ($(".filter-options").val()) {
          selectedFilter = "?filter=" + $(".filter-options").val();
        }
        window.location.href = window.location.pathname + selectedFilter;
      });

      $('#keyword-stats-table thead th.sortable').click(function() {
        var $this = $(this);
        var sortField = $this.attr('data-sort-field');
        var currentSortOrder = $this.attr('data-sort-order');
        var newSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';

        $this.attr('data-sort-order', newSortOrder);

        var queryParams = new URLSearchParams(window.location.search);
        queryParams.set('order', sortField);
        queryParams.set('sort', newSortOrder);
        window.location.search = queryParams.toString();
      });
    }
  };
})(jQuery, Drupal);
