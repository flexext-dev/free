/* FlexExt Articles Free Progress Bar */
document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('flx-reading-progress-bar')) {
        var bar = document.createElement('div');
        bar.id = 'flx-reading-progress-bar';
        document.body.appendChild(bar);

        window.addEventListener('scroll', function() {
            var winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            var scrolled = height > 0 ? (winScroll / height) * 100 : 0;
            bar.style.width = scrolled + '%';
        }, { passive: true });
    }
});
