<?php
require_once 'class/Logger.php';
require_once 'class/ErrorHandler.php';
session_start();
// Initialize error handling

new ErrorHandler();
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTZone Sale</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="static/css/styles.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <header class="py-4">
            <h1 class="text-center">HTZone Sale</h1>
        </header>

        <!-- Error Message Container -->
        <div id="error-message" class="alert alert-danger" style="display: none;"></div>

        <main>
            <!-- Carousels Section -->
            <section class="carousels-wrapper mb-5">
                <div id="carousel-1" class="carousel-container">
                    <h2 class="category-title mb-3">טוען קטגוריה...</h2>
                    <div class="carousel-items"></div>
                </div>

                <div id="carousel-2" class="carousel-container">
                    <h2 class="category-title mb-3">טוען קטגוריה...</h2>
                    <div class="carousel-items"></div>
                </div>

                <div id="carousel-3" class="carousel-container">
                    <h2 class="category-title mb-3">טוען קטגוריה...</h2>
                    <div class="carousel-items"></div>
                </div>
            </section>

            <!-- Filters Section -->
            <section class="filters-wrapper mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="category-filter" class="form-label">קטגוריה</label>
                        <select id="category-filter" class="form-select">
                            <option value="">כל הקטגוריות</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="brand-filter" class="form-label">מותג</label>
                        <select id="brand-filter" class="form-select">
                            <option value="">כל המותגים</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <form id="price-filter" class="row g-2">
                            <div class="col-6">
                                <label for="price-min" class="form-label">מחיר מינימלי</label>
                                <input type="number" id="price-min" class="form-control" placeholder="מ-">
                            </div>
                            <div class="col-6">
                                <label for="price-max" class="form-label">מחיר מקסימלי</label>
                                <input type="number" id="price-max" class="form-control" placeholder="עד-">
                            </div>
                        </form>
                    </div>

                    <div class="col-md-2">
                        <label for="sort-select" class="form-label">מיון</label>
                        <select id="sort-select" class="form-select">
                            <option value="name-ASC">שם (א-ת)</option>
                            <option value="name-DESC">שם (ת-א)</option>
                            <option value="price-ASC">מחיר (מהנמוך לגבוה)</option>
                            <option value="price-DESC">מחיר (מהגבוה לנמוך)</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Results Count -->
            <div id="total-count" class="text-muted mb-3"></div>

            <!-- Product Grid -->
            <section class="products-wrapper">
                <div id="product-list" class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4"></div>
                
                <!-- Loading Indicator -->
                <div id="loading-indicator" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">טוען...</span>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="static/js/scripts.js"></script>
</body>
</html>
