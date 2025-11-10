
const MIN_CHARS = 2;


function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}


function highlightMatch(text, query) {
    const escaped = escapeHtml(text);
    const queryEscaped = escapeHtml(query);
    const regex = new RegExp(`(${queryEscaped})`, 'gi');
    return escaped.replace(regex, '<mark>$1</mark>');
}


async function fetchSuggestions(query) {
    try {
        const response = await fetch(`autocomplete.php?q=${encodeURIComponent(query)}`);
        if (!response.ok) throw new Error('Erreur réseau');
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        return null;
    }
}


function createSuggestionItem(item, query, index) {
    const li = document.createElement('li');
    li.className = 'suggestion-item';
    li.setAttribute('data-id', item.id);
    li.setAttribute('data-name', item.nom_fr);
    li.setAttribute('data-index', index);

    const highlightedFr = highlightMatch(item.nom_fr, query);
    const highlightedLatin = item.nom_latin ? highlightMatch(item.nom_latin, query) : '';
    
    li.innerHTML = `
        <a href="element.php?id=${item.id}" class="suggestion-link">
            <span class="suggestion-name">${highlightedFr}</span>
            ${highlightedLatin ? `<span class="suggestion-latin"><em>${highlightedLatin}</em></span>` : ''}
        </a>
    `;

    return li;
}


function setupSearchBar(searchInputId, suggestionsContainerId, suggestionsListId, loadingId) {
    const searchInput = document.getElementById(searchInputId);
    const suggestionsContainer = document.getElementById(suggestionsContainerId);
    const suggestionsList = document.getElementById(suggestionsListId);
    const loadingIndicator = document.getElementById(loadingId);

    
    if (!searchInput || !suggestionsContainer || !suggestionsList) {
        console.log('Éléments manquants pour:', searchInputId);
        return;
    }

    
    let currentFocus = -1;

    function displaySuggestions(data, query) {
        suggestionsList.innerHTML = '';
        
        if (!data || (!data.startsWith?.length && !data.contains?.length)) {
            hideSuggestions();
            return;
        }

        let index = 0;

        
        if (data.startsWith?.length > 0) {
            const header = document.createElement('li');
            header.className = 'suggestions-header';
            header.textContent = 'Correspondances exactes';
            suggestionsList.appendChild(header);

            data.startsWith.forEach(item => {
                const li = createSuggestionItem(item, query, index++);
                
                li.addEventListener('click', function(e) {
                    e.preventDefault();
                    selectSuggestion(item);
                });
                suggestionsList.appendChild(li);
            });
        }

        
        if (data.startsWith?.length > 0 && data.contains?.length > 0) {
            const separator = document.createElement('li');
            separator.className = 'suggestions-separator';
            suggestionsList.appendChild(separator);
        }

        
        if (data.contains?.length > 0) {
            const header = document.createElement('li');
            header.className = 'suggestions-header';
            header.textContent = 'Autres résultats';
            suggestionsList.appendChild(header);

            data.contains.forEach(item => {
                const li = createSuggestionItem(item, query, index++);
                
                li.addEventListener('click', function(e) {
                    e.preventDefault();
                    selectSuggestion(item);
                });
                suggestionsList.appendChild(li);
            });
        }

        showSuggestions();
    }

    
    function selectSuggestion(item) {
        searchInput.value = item.nom_fr;
        hideSuggestions();
        window.location.href = `element.php?id=${item.id}`;
    }

    
    function showSuggestions() {
        suggestionsContainer.style.display = 'block';
    }

    function hideSuggestions() {
        suggestionsContainer.style.display = 'none';
        currentFocus = -1;
    }

    
    function showLoading() {
        if (loadingIndicator) loadingIndicator.style.display = 'block';
    }

    function hideLoading() {
        if (loadingIndicator) loadingIndicator.style.display = 'none';
    }

    
    async function performSearch(query) {
        if (query.length < MIN_CHARS) {
            hideSuggestions();
            hideLoading();
            return;
        }

        showLoading();
        const data = await fetchSuggestions(query);
        hideLoading();
        
        if (data) {
            displaySuggestions(data, query);
        }
    }

    
    function updateActiveItem(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === currentFocus);
        });
    }

    
    function handleKeyboard(e) {
        const items = suggestionsList.querySelectorAll('.suggestion-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentFocus = currentFocus < items.length - 1 ? currentFocus + 1 : 0;
            updateActiveItem(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentFocus = currentFocus > 0 ? currentFocus - 1 : items.length - 1;
            updateActiveItem(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentFocus >= 0 && items[currentFocus]) {
                const item = items[currentFocus];
                selectSuggestion({
                    id: item.getAttribute('data-id'),
                    nom_fr: item.getAttribute('data-name')
                });
            }
        } else if (e.key === 'Escape') {
            hideSuggestions();
        }
    }

    
    searchInput.addEventListener('input', (e) => {
        performSearch(e.target.value.trim());
    });

    searchInput.addEventListener('keydown', handleKeyboard);

    searchInput.addEventListener('focus', function() {
        if (this.value.length >= MIN_CHARS) {
            performSearch(this.value.trim());
        }
    });

   
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            hideSuggestions();
        }
    });

    console.log(' Barre de recherche initialisée:', searchInputId);
}


function initAutocompletion() {
   
    setupSearchBar(
        'search-input-header',
        'suggestions-container-header', 
        'suggestions-list-header',
        'search-loading-header'
    );

    
    setupSearchBar(
        'search-input-main',
        'suggestions-container-main',
        'suggestions-list-main', 
        'search-loading-main'
    );

    console.log(' Autocomplétion initialisée');
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAutocompletion);
} else {
    initAutocompletion();
}