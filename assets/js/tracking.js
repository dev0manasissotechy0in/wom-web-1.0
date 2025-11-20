(function() {
    const sessionId = getOrCreateSessionId();
    let startTime = Date.now();
    let currentPage = window.location.pathname;
    
    function getOrCreateSessionId() {
        let sid = sessionStorage.getItem('session_id');
        if (!sid) {
            sid = 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem('session_id', sid);
        }
        return sid;
    }
    
    function trackInteraction(type, data = {}) {
        fetch('/api/track-interaction.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                session_id: sessionId,
                page_url: currentPage,
                interaction_type: type,
                time_spent: Math.floor((Date.now() - startTime) / 1000),
                data: data
            })
        });
    }
    
    // Track page time
    window.addEventListener('beforeunload', () => {
        trackInteraction('page_exit');
    });
    
    // Track clicks
    document.addEventListener('click', (e) => {
        trackInteraction('click', {
            element: e.target.tagName,
            text: e.target.textContent.substring(0, 50)
        });
    });
    
    // Track scroll depth
    let maxScroll = 0;
    window.addEventListener('scroll', () => {
        const scrollPercent = Math.round(
            (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100
        );
        if (scrollPercent > maxScroll) {
            maxScroll = scrollPercent;
            if (maxScroll % 25 === 0) {
                trackInteraction('scroll', {depth: maxScroll});
            }
        }
    });
})();
