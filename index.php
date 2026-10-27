<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ShareTrack Public Page</title>

    <link rel="stylesheet" href="assets\css\style.css">
    <!-- Add icon library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css"
        integrity="sha512-QeR2VH+lsBE5LSAe1Q5EnTBbe7XTBubt8dG93Y7gidSgdMCr8nVqKcfKAMyN96SV8KDbZVTDXChatu5G2KQGzg=="
        crossorigin="anonymous" referrerpolicy="no-referrer">
</head>

<body>
    <header>
        <!-- Site Logo -->
        <div class="logo">ShareTrack</div>

        <!-- Navigation Bar -->
        <nav>
            <ul>
                <li><a href="javascript:void(0)">Home</a></li>
            </ul>
        </nav>
    </header>


    <main>
        <section class="container">
            <div class="container-content">
                <h1>Stay Informed with the Latest Updates</h1>

                <h4>
                    Welcome and discover the latest news, stories and insight from here.
                </h4>

                <section class="share-section">
                    <p>Share this page with your friends and family</p>

                    <div class="share-buttons">
                        <button type="button" class="share-btn facebook" data-platform="facebook">
                            <i class="fa-brands fa-facebook"></i>
                            Facebook
                        </button>

                        <button type="button" class="share-btn x" data-platform="x">
                            <i class="fa-brands fa-x-twitter"></i>
                        </button>

                        <button type="button" class="share-btn whatsapp" data-platform="whatsapp">
                            <i class="fa-brands fa-whatsapp"></i>
                            WhatsApp
                        </button>

                        <button type="button" class="share-btn telegram" data-platform="telegram">
                            <i class="fa-brands fa-telegram"></i>
                            Telegram
                        </button>

                        <button type="button" class="share-btn email" data-platform="email">
                            <i class="fa-solid fa-envelope"></i>
                            Email
                        </button>
                    </div>

                    <p id="share-message" role="status" aria-live="polite"></p>
                </section>
            </div>
        </section>
    </main>

    <footer class="footer">
        &copy; <span id="current-year"></span>
        ShareTrack. All rights reserved.
    </footer>

    <script src="assets/js/share.js"></script>
</body>

</html>