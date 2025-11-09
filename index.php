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
                <form action="recherche.php" method="GET" class="search-form" role="search">
                    <div class="search-wrapper">
                        <input 
                            type="text" 
                            name="search" 
                            id="search-input-header"
                            class="search-input"
                            placeholder="Rechercher un animal..."
                            autocomplete="off"
                            aria-label="Rechercher un animal"
                            aria-autocomplete="list"
                            role="combobox"
                            aria-expanded="false"
                            aria-owns="suggestions-list-header"
                            aria-controls="suggestions-list-header"
                        >
                        
                        <div class="search-loading" id="search-loading-header" aria-hidden="true">
                            <span class="loading-spinner"></span>
                        </div>
                        
                        <button type="submit" class="search-button" aria-label="Lancer la recherche">
                            🔍
                        </button>
                    </div>
                    
                    <div id="search-hint-header" class="sr-only">
                        Tapez au moins 2 caractères pour voir les suggestions.
                    </div>

                    <div 
                        id="suggestions-container-header" 
                        class="suggestions-container"
                        role="listbox"
                        aria-label="Suggestions de recherche"
                    >
                        <ul id="suggestions-list-header" class="suggestions-list">
                        </ul>
                    </div>
                </form>
            </div>
        </header>

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
                            id="search-input-main"
                            class="search-input"
                            placeholder="Rechercher un animal..."
                            autocomplete="off"
                            aria-label="Rechercher un animal"
                            aria-autocomplete="list"
                            role="combobox"
                            aria-expanded="false"
                            aria-owns="suggestions-list-main"
                            aria-controls="suggestions-list-main"
                        >
                        
                        <div class="search-loading" id="search-loading-main" aria-hidden="true">
                            <span class="loading-spinner"></span>
                        </div>
                        
                        <button type="submit" class="search-button" aria-label="Lancer la recherche">
                            🔍
                        </button>
                    </div>
                    
                    <div id="search-hint-main" class="sr-only">
                        Tapez au moins 2 caractères pour voir les suggestions.
                    </div>

                    <div 
                        id="suggestions-container-main" 
                        class="suggestions-container"
                        role="listbox"
                        aria-label="Suggestions de recherche"
                    >
                        <ul id="suggestions-list-main" class="suggestions-list">
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