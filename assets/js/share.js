// Social Share button clicks
document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('[data-platform]');
    const message = document.getElementById('share-message');

    buttons.forEach((button) => {
        button.addEventListener('click', async () => {
            const platform = button.dataset.platform;

            // Retrieve the current page URL and title for social sharing.
            const pageUrl = window.location.href;
            const title = document.title;

            // Prepare the text to accompany the shared URL.
            const text = 'Check out this page: ' + title;

            let shareUrl = '';

            switch (platform) {
                case 'facebook':
                    shareUrl =
                        'https://www.facebook.com/sharer/sharer.php?u=' +
                        encodeURIComponent(pageUrl);
                    break;

                case 'x':
                    shareUrl =
                        'https://twitter.com/intent/tweet?url=' +
                        encodeURIComponent(pageUrl) +
                        '&text=' + encodeURIComponent(text);
                    break;

                case 'whatsapp':
                    shareUrl =
                        'https://api.whatsapp.com/send?text=' +
                        encodeURIComponent(text + ' ' + pageUrl);
                    break;

                case 'telegram':
                    shareUrl =
                        'https://t.me/share/url?url=' +
                        encodeURIComponent(pageUrl) +
                        '&text=' + encodeURIComponent(text);
                    break;

                case 'email':
                    shareUrl =
                        'mailto:?subject=' +
                        encodeURIComponent(title) +
                        '&body=' +
                        encodeURIComponent(text + '\n' + pageUrl);
                    break;

                default:
                    return;
            }

            // Send the click event to the backend.
            try {
                const response = await fetch('api/track-share.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        platform: platform,
                        page_url: pageUrl
                    })
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Tracking request failed');
                }

                message.textContent = result.message;
            } catch (error) {
                console.error('Share tracking error:', error);
                message.textContent =
                    'Unable to record the share click. Opening share anyway.';
            }

            if (platform === 'email') {
                window.location.href = shareUrl;
            } else {
                window.open(
                    shareUrl,
                    '_blank',
                    'noopener,noreferrer'
                );
            }
        });
    });
});

// Footer current year
document.getElementById('current-year').textContent = new Date().getFullYear();