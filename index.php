    <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Moteur de recherche d'animaux avec autocomplétion">
    <title>Le dico des animaux </title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <nav class="nav-secondary">
                <button id="theme-toggle" type="button" class="nav-link" aria-label="Basculer le thème">
                    <span id="theme-icon" aria-hidden="true">🐣</span>
                </button>
            </nav>
              <div class="search-header">
                <form action="recherche.php" method="get" class="search-form-header" role="search">
                    <div class="search-wrapper-header">
                        <input 
                            type="text" 
                            name="search" 
                            id="search-input-header"
                            class="search-input-header"
                            placeholder="Rechercher un animal..."
                            autocomplete="off"
                            aria-label="Rechercher un animal"
                        >
                        <button type="submit" class="search-button-header" aria-label="Lancer la recherche">
                            🔍
                        </button>
                    </div>
                    
                    <div id="suggestions-container-header" class="suggestions-container" role="listbox">
                        <ul id="suggestions-list-header" class="suggestions-list"></ul>
                    </div>
                </form>
    </div>
        </header>

        <!-- 🔧 UNE SEULE section de recherche -->
        <main class="search-main">
            <div class="search-container">
                <h1 class="logo-title">
                    <span class="logo-icon" aria-hidden="true">🔎</span>
                    Le dico des animaux
                </h1>
                <p class="tagline">Découvre le nom des animaux en français et en latin</p>

                <form action="recherche.php" method="GET" class="search-form" role="search">
                    <div class="search-wrapper">
                        <input 
                            type="text" 
                            name="search" 
                            id="search-input"
                            class="search-input"
                            placeholder="Rechercher un animal..."
                            autocomplete="off"
                            aria-label="Rechercher un animal"
                            aria-autocomplete="list"
                            role="combobox"
                            aria-expanded="false"
                            aria-owns="suggestions-list"
                            aria-controls="suggestions-list"
                        >
                        
                        <div class="search-loading" id="search-loading" aria-hidden="true">
                            <span class="loading-spinner"></span>
                        </div>
                        
                        <button type="submit" class="search-button" aria-label="Lancer la recherche">
                            🔍
                        </button>
                    </div>
                    
                    <div id="search-hint" class="sr-only">
                        Tapez au moins 2 caractères pour voir les suggestions. 
                        Utilisez les flèches pour naviguer dans les suggestions.
                    </div>

                    <div 
                        id="suggestions-container" 
                        class="suggestions-container"
                        role="listbox"
                        aria-label="Suggestions de recherche"
                    >
                        <ul id="suggestions-list" class="suggestions-list">
                        </ul>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <script src="js/script.js" defer></script>
    <script src="js/completion.js" defer></script>
</body>
</html>