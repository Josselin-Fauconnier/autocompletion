(function() {
    'use strict';

    const MIN_CHARS = 2;
    const DEBOUNCE_DELAY = 300;

    
    let currentFocus = -1;
    let timeoutId = null;

    // Éléments DOM
    const searchInput = document.getElementById('search-input');
    const suggestionsContainer = document.getElementById('suggestions-container');
    const suggestionsList = document.getElementById('suggestions-list');
    const loadingIndicator = document.getElementById('search-loading');

    
    function debounce(func, delay) {
        return function(...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }

    
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
    
    function displaySuggestions(data, query) {
        suggestionsList.innerHTML = '';
        
        if (!data || (!data.startsWith?.length && !data.contains?.length)) {
            hideSuggestions();
            return;
        }

        let index = 0;

        // Section "Commence par"
        if (data.startsWith?.length > 0) {
            const header = document.createElement('li');
            header.className = 'suggestions-header';
            header.textContent = 'Correspondances exactes';
            suggestionsList.appendChild(header);

            data.startsWith.forEach(item => {
                const li = createSuggestionItem(item, query, index++);
                suggestionsList.appendChild(li);
            });
        }

        // Séparateur
        if (data.startsWith?.length > 0 && data.contains?.length > 0) {
            const separator = document.createElement('li');
            separator.className = 'suggestions-separator';
            suggestionsList.appendChild(separator);
        }

        // Section "Contient"
        if (data.contains?.length > 0) {
            const header = document.createElement('li');
            header.className = 'suggestions-header';
            header.textContent = 'Autres résultats';
            suggestionsList.appendChild(header);

            data.contains.forEach(item => {
                const li = createSuggestionItem(item, query, index++);
                suggestionsList.appendChild(li);
            });
        }

        showSuggestions();
    }

    // Créer un élément de suggestion
    function createSuggestionItem(item, query, index) {
        const li = document.createElement('li');
        li.className = 'suggestion-item';
        li.setAttribute('data-id', item.id);
        li.setAttribute('data-name', item.name);
        li.setAttribute('data-index', index);

        const highlightedName = highlightMatch(item.name, query);
        li.innerHTML = `
            <a href="element.php?id=${item.id}" class="suggestion-link">
                <span class="suggestion-name">${highlightedName}</span>
            </a>
        `;

        // Gestion du clic
        li.addEventListener('click', function(e) {
            e.preventDefault();
            selectSuggestion(item);
        });

        return li;
    }

    // Sélectionner une suggestion
    function selectSuggestion(item) {
        searchInput.value = item.name;
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

    // Fonction de recherche avec debounce
    const performSearch = debounce(async function(query) {
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
    }, DEBOUNCE_DELAY);

    
    function handleKeyboard(e) {
        const items = suggestionsList.querySelectorAll('.suggestion-item');
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                currentFocus = currentFocus < items.length - 1 ? currentFocus + 1 : 0;
                updateActiveItem(items);
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                currentFocus = currentFocus > 0 ? currentFocus - 1 : items.length - 1;
                updateActiveItem(items);
                break;
                
            case 'Enter':
                e.preventDefault();
                if (currentFocus >= 0 && items[currentFocus]) {
                    const item = items[currentFocus];
                    selectSuggestion({
                        id: item.getAttribute('data-id'),
                        name: item.getAttribute('data-name')
                    });
                }
                break;
                
            case 'Escape':
                hideSuggestions();
                break;
        }
    }

   
    function updateActiveItem(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === currentFocus);
        });
    }

    
    function init() {
        if (!searchInput || !suggestionsContainer) {
            console.error('Éléments requis non trouvés');
            return;
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

        console.log('Autocomplétion initialisée');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
