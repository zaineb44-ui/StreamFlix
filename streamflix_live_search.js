
// Live Search Suggestions for Movies and TV Shows
let searchTimeout;

document.querySelector('.search-input').addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    const query = e.target.value.trim();

    if (query.length < 2) {
        document.getElementById('live-suggestions')?.remove();
        return;
    }

    searchTimeout = setTimeout(() => {
        fetch(`https://api.themoviedb.org/3/search/multi?api_key=f5c8d5add995935daa212039d7b34e5d&query=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                let suggestions = document.getElementById('live-suggestions');
                if (!suggestions) {
                    suggestions = document.createElement('div');
                    suggestions.id = 'live-suggestions';
                    suggestions.style.position = 'absolute';
                    suggestions.style.backgroundColor = '#fff';
                    suggestions.style.color = '#000';
                    suggestions.style.width = '100%';
                    suggestions.style.maxHeight = '300px';
                    suggestions.style.overflowY = 'auto';
                    suggestions.style.zIndex = '200';
                    document.querySelector('.search-container').appendChild(suggestions);
                }

                suggestions.innerHTML = '';
                data.results.slice(0, 10).forEach(item => {
                    const div = document.createElement('div');
                    div.textContent = item.title || item.name;
                    div.style.padding = '10px';
                    div.style.cursor = 'pointer';
                    div.style.borderBottom = '1px solid #ccc';
                    div.addEventListener('click', () => {
                        document.querySelector('.search-input').value = item.title || item.name;
                        document.getElementById('live-suggestions')?.remove();
                    });
                    suggestions.appendChild(div);
                });
            });
    }, 300);
});

document.addEventListener('click', () => {
    document.getElementById('live-suggestions')?.remove();
});
