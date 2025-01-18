// Global variables
let currentPage = 1;
const itemsPerPage = 10;
let isLoading = false;
let hasMoreItems = true;

// Initialize carousels and load initial data
$(document).ready(function() {
    // Load top categories for carousels
    loadTopCategories();
    
    // Load initial items grid
    loadItems();
    
    // Load filter options
    loadFilterOptions();
    
    // Setup event handlers
    setupEventHandlers();
    
    // Setup infinite scroll
    setupInfiniteScroll();
});

function loadTopCategories() {
    $.ajax({
        url: 'ajax/ajax.php',
        method: 'POST',
        data: { 
            act: 'getTopCategories',
            limit: 3
        },
        success: function(response) {
            if (response.status === 'success') {
                response.data.forEach((category, index) => {
                    // Update carousel titles
                    $(`#carousel-${index + 1} h2`).text(category.name);
                    
                    // Load items for this category
                    loadCategoryItems(category.id, `#carousel-${index + 1} .carousel-items`);
                });
            }
        },
        error: handleAjaxError
    });
}

function loadCategoryItems(categoryId, container) {
    $.ajax({
        url: 'ajax/ajax.php',
        method: 'POST',
        data: {
            act: 'getCategoryItems',
            category_id: categoryId,
            limit: 10
        },
        success: function(response) {
            if (response.status === 'success') {
                const $container = $(container);
                $container.empty();
                
                response.data.items.forEach(item => {
                    $container.append(createItemCard(item));
                });
                
                // Initialize carousel functionality
                initializeCarousel($container);
            }
        },
        error: handleAjaxError
    });
}

function loadItems(options = {}, append = false) {
    if (isLoading || (!append && !hasMoreItems)) return;
    
    isLoading = true;
    $('#loading-indicator').show();
    
    const params = {
        act: 'getItems',
        page: append ? currentPage : 1,
        limit: itemsPerPage,
        ...options
    };
    
    $.ajax({
        url: 'ajax/ajax.php',
        method: 'POST',
        data: params,
        success: function(response) {
            if (response.status === 'success') {
                const $container = $('#product-list');
                
                if (!append) {
                    $container.empty();
                    currentPage = 1;
                }
                
                response.data.items.forEach(item => {
                    $container.append(createItemCard(item));
                });
                
                hasMoreItems = response.data.items.length === itemsPerPage;
                if (hasMoreItems) currentPage++;
                
                // Update total count display if available
                if (response.data.total) {
                    $('#total-count').text(`מציג ${response.data.items.length} מתוך ${response.data.total} מוצרים`);
                }
            }
        },
        error: handleAjaxError,
        complete: function() {
            isLoading = false;
            $('#loading-indicator').hide();
        }
    });
}

function loadFilterOptions() {
    // Load categories for filter
    $.ajax({
        url: 'ajax/ajax.php',
        method: 'POST',
        data: { act: 'getCategories' },
        success: function(response) {
            if (response.status === 'success') {
                const $select = $('#category-filter');
                response.data.forEach(category => {
                    $select.append(new Option(category.name, category.id));
                });
            }
        }
    });
    
    // Load brands for filter
    $.ajax({
        url: 'ajax/ajax.php',
        method: 'POST',
        data: { act: 'getBrands' },
        success: function(response) {
            if (response.status === 'success') {
                const $select = $('#brand-filter');
                response.data.forEach(brand => {
                    $select.append(new Option(brand, brand));
                });
            }
        }
    });
    
    // Load price range
    $.ajax({
        url: 'ajax/ajax.php',
        method: 'POST',
        data: { act: 'getPriceRange' },
        success: function(response) {
            if (response.status === 'success') {
                $('#price-min').attr({
                    'min': response.data.min_price,
                    'max': response.data.max_price,
                    'placeholder': `מ-${response.data.min_price}`
                });
                $('#price-max').attr({
                    'min': response.data.min_price,
                    'max': response.data.max_price,
                    'placeholder': `עד-${response.data.max_price}`
                });
            }
        }
    });
}

function setupEventHandlers() {
    // Category filter change
    $('#category-filter').on('change', function() {
        loadItems({ category: $(this).val() });
    });
    
    // Sort select change
    $('#sort-select').on('change', function() {
        const [field, direction] = $(this).val().split('-');
        loadItems({ 
            sort_field: field, 
            sort_direction: direction 
        });
    });
    
    // Price filter form submit
    $('#price-filter').on('submit', function(e) {
        e.preventDefault();
        loadItems({
            price_min: $('#price-min').val(),
            price_max: $('#price-max').val()
        });
    });
    
    // Brand filter change
    $('#brand-filter').on('change', function() {
        loadItems({ brand: $(this).val() });
    });
}

function setupInfiniteScroll() {
    $(window).on('scroll', function() {
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 500) {
            loadItems({}, true);
        }
    });
}

function createItemCard(item) {
    return $('<div>').addClass('product-item').append(
        $('<img>').addClass('product-image')
            .attr('src', item.image_url)
            .attr('alt', item.name)
            .attr('loading', 'lazy'),
        $('<h3>').text(item.name),
        $('<p>').addClass('brand').text(item.brand),
        $('<p>').addClass('price').text(new Intl.NumberFormat('he-IL', {
            style: 'currency',
            currency: 'ILS'
        }).format(item.price))
    );
}

function initializeCarousel($container) {
    let scrolling = false;
    
    // Add scroll buttons if needed
    const $wrapper = $container.parent();
    if ($container[0].scrollWidth > $container.width()) {
        $wrapper.append(
            $('<button>').addClass('carousel-nav prev').text('‹'),
            $('<button>').addClass('carousel-nav next').text('›')
        );
        
        // Scroll handlers
        $wrapper.find('.carousel-nav.prev').on('click', () => {
            if (!scrolling) {
                scrolling = true;
                $container.animate({
                    scrollLeft: '-=200'
                }, 300, () => { scrolling = false; });
            }
        });
        
        $wrapper.find('.carousel-nav.next').on('click', () => {
            if (!scrolling) {
                scrolling = true;
                $container.animate({
                    scrollLeft: '+=200'
                }, 300, () => { scrolling = false; });
            }
        });
    }
}

function handleAjaxError(jqXHR, textStatus, errorThrown) {
    console.error('AJAX error:', textStatus, errorThrown);
    // Show error message to user
    $('#error-message')
        .text('אירעה שגיאה בטעינת הנתונים. אנא נסה שוב מאוחר יותר.')
        .show()
        .delay(5000)
        .fadeOut();
}
