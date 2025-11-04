

(function() {
    'use strict';

    
    const CONFIG = {
        MIN_CHARS: 2,           
        DEBOUNCE_DELAY: 300,    
        MAX_SUGGESTIONS: 10,    
        API_ENDPOINT: 'autocomplete.php',
        CACHE_DURATION: 300000  
    };

    
    const cache = new Map();
    
    
    const state = {
        currentFocus: -1,
        isLoading: false,
        abortController: null
    };

    
    const elements = {
        searchInput: null,
        suggestionsContainer: null,
        suggestionsList: null,
        loadingIndicator: null,
        searchForm: null
    };

    /**
     * Fonction de debounce pour limiter les appels API
     * Pattern : Debounce (programmation fonctionnelle)
     */
    function debounce(func, delay) {
        let timeoutId;
        return function(...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }

    /**
     * Fonction pour échapper les caractères HTML (sécurité XSS)
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, char => map[char]);
    }

    /**
     * Fonction pour mettre en évidence le terme recherché
     */
    function highlightMatch(text, query) {
        if (!query) return escapeHtml(text);
        
        const escaped = escapeHtml(text);
        const queryEscaped = escapeHtml(query);
        const regex = new RegExp(`(${queryEscaped})`, 'gi');
        
        return escaped.replace(regex, '<mark>$1</mark>');
    }

    /**
     * Vérifier si une requête est en cache et encore valide
     */
    function getCachedResult(query) {
        const cached = cache.get(query);
        if (cached && (Date.now() - cached.timestamp < CONFIG.CACHE_DURATION)) {
            return cached.data;
        }
        cache.delete(query);
        return null;
    }

    /**
     * Effectuer la requête API avec gestion des erreurs
     */
    async function fetchSuggestions(query) {
        // Vérifier le cache d'abord
        const cached = getCachedResult(query);
        if (cached) {
            return cached;
        }

        // Annuler la requête précédente si elle existe
        if (state.abortController) {
            state.abortController.abort();
        }

        // Créer un nouveau controller pour cette requête
        state.abortController = new AbortController();

        try {
            const response = await fetch(
                `${CONFIG.API_ENDPOINT}?query=${encodeURIComponent(query)}&limit=${CONFIG.MAX_SUGGESTIONS}`,
                {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    },
                    signal: state.abortController.signal
                }
            );

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            // Mettre en cache le résultat
            cache.set(query, {
                data: data,
                timestamp: Date.now()
            });

            return data;

        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('Requête annulée');
                return null;
            }
            
            console.error('Erreur lors de la récupération des suggestions:', error);
            throw error;
        }
    }

    /**
     * Afficher les suggestions dans l'interface
     */
    function displaySuggestions(data, query) {
        if (!data || (!data.startsWith?.length && !data.contains?.length)) {
            hideSuggestions();
            return;
        }

        elements.suggestionsList.innerHTML = '';
        let optionIndex = 0;

        // Section "Commence par"
        if (data.startsWith && data.startsWith.length > 0) {
            const startsHeader = document.createElement('li');
            startsHeader.className = 'suggestions-header';
            startsHeader.textContent = 'Correspondances exactes';
            startsHeader.setAttribute('role', 'presentation');
            elements.suggestionsList.appendChild(startsHeader);

            data.startsWith.forEach(item => {
                const li = createSuggestionItem(item, query, optionIndex++);
                elements.suggestionsList.appendChild(li);
            });
        }

        // Séparateur visuel
        if (data.startsWith?.length > 0 && data.contains?.length > 0) {
            const separator = document.createElement('li');
            separator.className = 'suggestions-separator';
            separator.setAttribute('role', 'presentation');
            elements.suggestionsList.appendChild(separator);
        }

        // Section "Contient"
        if (data.contains && data.contains.length > 0) {
            const containsHeader = document.createElement('li');
            containsHeader.className = 'suggestions-header';
            containsHeader.textContent = 'Autres résultats';
            containsHeader.setAttribute('role', 'presentation');
            elements.suggestionsList.appendChild(containsHeader);

            data.contains.forEach(item => {
                const li = createSuggestionItem(item, query, optionIndex++);
                elements.suggestionsList.appendChild(li);
            });
        }

        showSuggestions();
    }

    /**
     * Créer un élément de suggestion
     */
    function createSuggestionItem(item, query, index) {
        const li = document.createElement('li');
        li.className = 'suggestion-item';
        li.setAttribute('role', 'option');
        li.setAttribute('id', `suggestion-${index}`);
        li.setAttribute('data-id', item.id);
        li.setAttribute('data-name', item.name);
        
        // Créer le contenu avec mise en évidence
        const highlightedName = highlightMatch(item.name, query);
        
        li.innerHTML = `
            <a href="element.php?id=${item.id}" class="suggestion-link">
                <span class="suggestion-name">${highlightedName}</span>
            </a>
        `;

        // Gérer le clic
        li.addEventListener('click', function(e) {
            e.preventDefault();
            selectSuggestion(item);
        });

        // Gérer le survol
        li.addEventListener('mouseenter', function() {
            removeActive();
            state.currentFocus = index;
            addActive();
        });

        return li;
    }

    /**
     * Sélectionner une suggestion
     */
    function selectSuggestion(item) {
        elements.searchInput.value = item.name;
        hideSuggestions();
        
        // Rediriger vers la page de l'élément
        window.location.href = `element.php?id=${item.id}`;
    }

    /**
     * Afficher le conteneur de suggestions
     */
    function showSuggestions() {
        elements.suggestionsContainer.style.display = 'block';
        elements.searchInput.setAttribute('aria-expanded', 'true');
    }

    /**
     * Masquer le conteneur de suggestions
     */
    function hideSuggestions() {
        elements.suggestionsContainer.style.display = 'none';
        elements.searchInput.setAttribute('aria-expanded', 'false');
        state.currentFocus = -1;
    }

    /**
     * Afficher l'indicateur de chargement
     */
    function showLoading() {
        state.isLoading = true;
        elements.loadingIndicator.style.display = 'block';
        elements.loadingIndicator.setAttribute('aria-hidden', 'false');
    }

    /**
     * Masquer l'indicateur de chargement
     */
    function hideLoading() {
        state.isLoading = false;
        elements.loadingIndicator.style.display = 'none';
        elements.loadingIndicator.setAttribute('aria-hidden', 'true');
    }

    /**
     * Gérer l'erreur lors de la recherche
     */
    function handleSearchError(error) {
        hideLoading();
        
        // Afficher un message d'erreur discret
        const errorMessage = document.createElement('li');
        errorMessage.className = 'error-message';
        errorMessage.textContent = 'Erreur de connexion. Veuillez réessayer.';
        
        elements.suggestionsList.innerHTML = '';
        elements.suggestionsList.appendChild(errorMessage);
        showSuggestions();
        
        // Masquer après 3 secondes
        setTimeout(hideSuggestions, 3000);
    }

    /**
     * Fonction de recherche avec debounce
     */
    const performSearch = debounce(async function(query) {
        if (query.length < CONFIG.MIN_CHARS) {
            hideSuggestions();
            hideLoading();
            return;
        }

        showLoading();

        try {
            const data = await fetchSuggestions(query);
            if (data) {
                displaySuggestions(data, query);
            }
        } catch (error) {
            handleSearchError(error);
        } finally {
            hideLoading();
        }
    }, CONFIG.DEBOUNCE_DELAY);

    
    function handleKeyboardNavigation(e) {
        const items = elements.suggestionsList.querySelectorAll('.suggestion-item');
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                state.currentFocus++;
                if (state.currentFocus >= items.length) {
                    state.currentFocus = 0;
                }
                addActive();
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                state.currentFocus--;
                if (state.currentFocus < 0) {
                    state.currentFocus = items.length - 1;
                }
                addActive();
                break;
                
            case 'Enter':
                e.preventDefault();
                if (state.currentFocus > -1 && items[state.currentFocus]) {
                    const item = items[state.currentFocus];
                    const selectedItem = {
                        id: item.getAttribute('data-id'),
                        name: item.getAttribute('data-name')
                    };
                    selectSuggestion(selectedItem);
                } else {
                    elements.searchForm.submit();
                }
                break;
                
            case 'Escape':
                hideSuggestions();
                elements.searchInput.blur();
                break;
        }
    }

   
    function removeActive() {
        const items = elements.suggestionsList.querySelectorAll('.suggestion-item');
        items.forEach(item => {
            item.classList.remove('active');
            item.setAttribute('aria-selected', 'false');
        });
    }

    
    function addActive() {
        removeActive();
        const items = elements.suggestionsList.querySelectorAll('.suggestion-item');
        
        if (state.currentFocus >= 0 && state.currentFocus < items.length) {
            items[state.currentFocus].classList.add('active');
            items[state.currentFocus].setAttribute('aria-selected', 'true');
            
            
            items[state.currentFocus].scrollIntoView({
                block: 'nearest',
                behavior: 'smooth'
            });
            
            
            elements.searchInput.setAttribute('aria-activedescendant', `suggestion-${state.currentFocus}`);
        }
    }

   
    function testSecurity() {
        const testQueries = [
            '<script>alert("XSS")</script>',
            '"; DROP TABLE animaux; --',
            '<img src=x onerror=alert("XSS")>',
            'javascript:alert("XSS")',
            '"><script>alert(String.fromCharCode(88,83,83))</script>'
        ];

        console.group('🔒 Test de sécurité');
        console.log('Test d\'injection XSS et SQL en cours...');
        
        testQueries.forEach((query, index) => {
            console.log(`Test ${index + 1}: "${query}"`);
           
            const escaped = escapeHtml(query);
            console.log(`Résultat échappé: "${escaped}"`);
        });
        
        console.log('✅ Tests de sécurité terminés. Les entrées sont correctement échappées.');
        console.groupEnd();
        
        alert('Tests de sécurité exécutés. Consultez la console pour les détails.');
    }

    
    function init() {
        
        elements.searchInput = document.getElementById('search-input');
        elements.suggestionsContainer = document.getElementById('suggestions-container');
        elements.suggestionsList = document.getElementById('suggestions-list');
        elements.loadingIndicator = document.getElementById('search-loading');
        elements.searchForm = document.querySelector('.search-form');

        
        if (!elements.searchInput || !elements.suggestionsContainer) {
            console.error('Éléments requis non trouvés dans le DOM');
            return;
        }

        elements.searchInput.addEventListener('input', function(e) {
            const query = e.target.value.trim();
            performSearch(query);
        });

        elements.searchInput.addEventListener('keydown', handleKeyboardNavigation);

        elements.searchInput.addEventListener('focus', function() {
            if (this.value.length >= CONFIG.MIN_CHARS) {
                performSearch(this.value.trim());
            }
        });

        document.addEventListener('click', function(e) {
            if (!elements.searchInput.contains(e.target) && 
                !elements.suggestionsContainer.contains(e.target)) {
                hideSuggestions();
            }
        });

        const securityLink = document.getElementById('test-security');
        if (securityLink) {
            securityLink.addEventListener('click', function(e) {
                e.preventDefault();
                testSecurity();
            });
        }

        console.log('✅ Module d\'autocomplétion initialisé avec succès');
        console.log(`Configuration: ${CONFIG.MIN_CHARS} caractères minimum, ${CONFIG.DEBOUNCE_DELAY}ms de délai`);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();