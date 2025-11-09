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
            
            const data = await response.json();
            console.log('Données reçues:', data); 
            return data;
            
        } catch (error) {
            console.error('Erreur fetch:', error);
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

    // 🔧 CORRECTION: Utiliser nom_fr au lieu de name
    function createSuggestionItem(item, query, index) {
        const li = document.createElement('li');
        li.className = 'suggestion-item';
        li.setAttribute('data-id', item.id);
        li.setAttribute('data-name', item.nom_fr); // ✅ Utiliser nom_fr
        li.setAttribute('data-latin', item.nom_latin || ''); // Bonus: nom latin
        li.setAttribute('data-index', index);

        // ✅ Mise en évidence sur nom_fr ET nom_latin
        const highlightedFr = highlightMatch(item.nom_fr, query);
        const highlightedLatin = item.nom_latin ? highlightMatch(item.nom_latin, query) : '';
        
        li.innerHTML = `
            <a href="element.php?id=${item.id}" class="suggestion-link">
                <span class="suggestion-name">${highlightedFr}</span>
                ${highlightedLatin ? `<span class="suggestion-latin"><em>${highlightedLatin}</em></span>` : ''}
                <span class="suggestion-category">${translateCategory(item.categorie)}</span>
            </a>
        `;

        // Gestion du clic
        li.addEventListener('click', function(e) {
            e.preventDefault();
            selectSuggestion({
                id: item.id,
                name: item.nom_fr 
            });
        });

        return li;
    }

    // Fonction pour traduire les catégories (comme dans PHP)
    function translateCategory(category) {
        const translations = {
            'mammifere': 'Mammifère',
            'oiseau': 'Oiseau', 
            'poisson': 'Poisson',
            'reptile': 'Reptile',
            'insecte': 'Insecte'
        };
        return translations[category] || category;
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
        } else {
            console.error('Pas de données reçues');
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
            console.log('searchInput:', searchInput);
            console.log('suggestionsContainer:', suggestionsContainer);
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

        // Cacher suggestions au clic extérieur
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                hideSuggestions();
            }
        });

        console.log('✅ Autocomplétion initialisée avec succès');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();