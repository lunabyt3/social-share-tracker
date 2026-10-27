<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

// 1. Run a single query with conditional counts
$stmt = $pdo->query("
    SELECT 
        COUNT(*) AS total_shares,
        SUM(CASE WHEN platform = 'facebook' THEN 1 ELSE 0 END) AS fb_shares,
        SUM(CASE WHEN platform = 'x' THEN 1 ELSE 0 END) AS x_shares,
        SUM(CASE WHEN platform = 'whatsapp' THEN 1 ELSE 0 END) AS wp_shares,
        SUM(CASE WHEN platform = 'telegram' THEN 1 ELSE 0 END) AS tg_shares,
        SUM(CASE WHEN platform = 'email' THEN 1 ELSE 0 END) AS email_shares
    FROM social_shares
");

// 2. Fetch the entire row as an associative array
$shares = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Cast the results to integers
$totalShares = (int) $shares['total_shares'];
$total_FB_shares = (int) $shares['fb_shares'];
$total_X_shares = (int) $shares['x_shares'];
$total_WP_shares = (int) $shares['wp_shares'];
$total_TG_shares = (int) $shares['tg_shares'];
$total_Email_shares = (int) $shares['email_shares'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ShareTrack Admin Dashboard</title>
    <!-- Add icon library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css"
        integrity="sha512-QeR2VH+lsBE5LSAe1Q5EnTBbe7XTBubt8dG93Y7gidSgdMCr8nVqKcfKAMyN96SV8KDbZVTDXChatu5G2KQGzg=="
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #10264f;
        }

        /* Layout the header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: white;
            color: #10264f;
            border: 1px solid #e5eaf0;
        }

        .logo {

            font-size: 30px;
            font-weight: 800;
            color: #10264f;
            letter-spacing: -1px;
        }

        /* Remove bullets and line up list items side-by-side */
        nav ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 20px;
        }

        /* Style the links */
        nav a {
            color: #10264f;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
        }

        /* Add a hover effect */
        nav a:hover {
            color: #ff9900;
        }

        main {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 30px;
            background: #f4f6f9;
            color: #222;
        }

        .panel {
            background: white;
            padding: 24px;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: end;
        }

        label {
            display: grid;
            gap: 6px;
        }

        input,
        select,
        button {
            padding: 9px;
        }

        button {
            cursor: pointer;
        }

        .overview_board,
        .charts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        #status {
            color: #555;
        }

        .text-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .text-container i {
            font-size: 32px;
            text-decoration: none;
            margin: 0 10px;
            /* Adds spacing between icons */
        }
    </style>
</head>

<body>
    <header>
        <!-- Site Logo -->
        <div class="logo">ShareTrack</div>

        <!-- Navigation Bar -->
        <nav>
            <ul>
                <li><a href="logout.php">Logout <i class="fa fa-sign-out" aria-hidden="true"></i></a>
                </li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Social Share Analytics</h1>
        <div class="overview_board">
            <section class="panel">
                <div class="text-container">
                    <h3>Total Shares</h3><i class="fa-solid fa-share-nodes"></i>
                </div>
                <p><?= $totalShares ?></p>
            </section>
            <section class="panel">
                <div class="text-container">
                    <h3>Facebook Shares</h3><i class="fa-brands fa-facebook-f" style="color: #0866FF;"></i>
                </div>
                <p><?= $total_FB_shares ?></p>
            </section>
            <section class="panel">
                <div class="text-container">
                    <h3>X Shares</h3><i class="fa-brands fa-x-twitter" style="color: #000000;"></i>
                </div>
                <p><?= $total_X_shares ?></p>
            </section>
            <section class="panel">
                <div class="text-container">
                    <h3>Whatsapp Shares</h3><i class="fa-brands fa-whatsapp" style="color: #25D366;"></i>
                </div>
                <p> <?= $total_WP_shares ?></p>
            </section>
            <section class="panel">
                <div class="text-container">
                    <h3>Telegram Shares</h3><i class="fa-brands fa-telegram" style="color: #0088CC;"></i>
                </div>
                <p><?= $total_TG_shares ?></p>
            </section>
            <section class="panel">
                <div class="text-container">
                    <h3>Email Shares</h3><i class="fa-regular fa-envelope" style="color: #657786;"></i>
                </div>
                <p><?= $total_Email_shares ?> </p>
            </section>
        </div>
        <section class="panel">
            <h2>Filter analytics</h2>
            <form id="filters" class="filters">
                <label>
                    Start date
                    <input type="date" name="start_date"
                        value="<?= htmlspecialchars(date('Y-m-d', strtotime('-6 days'))) ?>" required>
                </label>
                <label>
                    End date
                    <input type="date" name="end_date" value="<?= htmlspecialchars(date('Y-m-d')) ?>" required>
                </label>
                <label>
                    Platform
                    <select name="platform">
                        <option value="all">All platforms</option>
                        <option value="facebook">Facebook</option>
                        <option value="x">X</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="telegram">Telegram</option>
                        <option value="email">Email</option>
                    </select>
                </label>
                <button type="submit">Apply Filters</button>
                <button type="button" id="clearFilters">Clear Filters</button>
            </form>
            <p id="status" role="status"></p>
        </section>
        <div class="charts">
            <section class="panel">
                <h2>Clicks by platform</h2>
                <div class="chart-container">
                    <canvas id="platformChart"></canvas>
                </div>
            </section>
            <section class="panel">
                <h2>Clicks by date</h2>
                <div class="chart-container">
                    <canvas id="dailyChart"></canvas>
                </div>
            </section>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const form = document.getElementById('filters');
        const status = document.getElementById('status');
        const clearButton = document.getElementById('clearFilters');

        clearButton.addEventListener('click', () => {
            // Reset date range to the default last 7 days
            form.elements.start_date.value = '<?= date('Y-m-d', strtotime('-6 days')) ?>';
            form.elements.end_date.value = '<?= date('Y-m-d') ?>';

            // Reset platform to all
            form.elements.platform.value = 'all';

            // Reload analytics with default filters
            loadAnalytics();
        });

        let platformChart;
        let dailyChart;

        async function loadAnalytics() {
            const query = new URLSearchParams(
                new FormData(form)
            );

            status.textContent = 'Loading analytics...';

            try {
                const response = await fetch(
                    'analytics.php?' + query.toString()
                );

                if (!response.ok) {
                    throw new Error('Failed to load analytics');
                }

                const data = await response.json();
                console.log('Response status:', response.status);
                console.log('Analytics data:', data);

                // BarChart (Platform)
                const platformLabels = data.platform_stats.map(
                    item => item.platform
                );
                const platformValues = data.platform_stats.map(
                    item => Number(item.total)
                );

                // LineChart (Daily)
                const dailyLabels = data.daily_stats.map(
                    item => item.share_date
                );
                const dailyValues = data.daily_stats.map(
                    item => Number(item.total)
                );

                if (platformChart) platformChart.destroy();
                if (dailyChart) dailyChart.destroy();

                // BarChart-Apply color based on the specific X label text
                const platformColors = {
                    facebook: {
                        border: 'rgba(8, 102, 255, 1)',
                        background: 'rgba(8, 102, 255, 0.2)'
                    },

                    x: {
                        border: 'rgba(0, 0, 0, 1)',
                        background: 'rgba(0, 0, 0, 0.2)'
                    },

                    whatsapp: {
                        border: 'rgba(37, 211, 102, 1)',
                        background: 'rgba(37, 211, 102, 0.2)'
                    },

                    telegram: {
                        border: 'rgba(0, 136, 204, 1)',
                        background: 'rgba(0, 136, 204, 0.2)'
                    },

                    email: {
                        border: 'rgba(101, 119, 134, 1)',
                        background: 'rgba(101, 119, 134, 0.2)'
                    }
                };
                const defaultColor = {
                    border: 'rgba(100, 116, 139, 1)',
                    background: 'rgba(100, 116, 139, 0.2)'
                };

                // BarChart (Platform)
                platformChart = new Chart(
                    document.getElementById('platformChart'),
                    {
                        type: 'bar',
                        data: {
                            labels: platformLabels,
                            datasets: [{
                                label: 'Share clicks',
                                data: platformValues,
                                borderColor: function (context) {
                                    const label = context.chart.data.labels[context.dataIndex]
                                        ?.toLowerCase();

                                    return (platformColors[label] || defaultColor).border;
                                },

                                backgroundColor: function (context) {
                                    const label = context.chart.data.labels[context.dataIndex]
                                        ?.toLowerCase();

                                    return (platformColors[label] || defaultColor).background;
                                },

                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    }
                );

                // LineChart (Daily)
                dailyChart = new Chart(
                    document.getElementById('dailyChart'),
                    {
                        type: 'line',
                        data: {
                            labels: dailyLabels,
                            datasets: [{
                                label: 'Share clicks',
                                data: dailyValues,
                                tension: 0.25
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    }
                );

                status.textContent = 'Analytics updated.';
            } catch (error) {
                console.error(error);
                status.textContent =
                    'Unable to load analytics. Check your PHP and database configuration.';
            }
        }

        // ApplyFilters button
        form.addEventListener('submit', event => {
            event.preventDefault();

            if (form.elements.start_date.value >
                form.elements.end_date.value) {
                status.textContent =
                    'Start date must not be later than end date.';
                return;
            }

            loadAnalytics();
        });

        loadAnalytics();
    </script>
</body>

</html>