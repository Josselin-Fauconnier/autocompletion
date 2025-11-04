    <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Moteur de recherche d'animaux avec autocomplétion">
    <title> Recherche d'animaux</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        
        <header class="header">
            <button id="theme-toggle" aria-label="Basculer entre le mode sombre et clair">
             <span id="theme-icon">🐣</span>
             </button>
            <nav class="nav-secondary">
                <a href="index.php" class="nav-link">Accueil</a>
            </nav>
        </header>

        
        <main class="search-main">
            <div class="search-container">
                <h1 class="logo-title">
                    Recherche des noms francais/latin des animaux
                </h1>
                <p class="tagline">Découvre le nom des animaux en francais et en latin</p>

                
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
                            aria-describedby="search-hint"
                            aria-autocomplete="list"
                            aria-controls="suggestions-list"
                            aria-expanded="false"
                        >
                        
                
                        <div class="search-loading" id="search-loading" aria-hidden="true">
                            <span class="loading-spinner"></span>
                        </div>
                        
                        <button type="submit" class="search-button" aria-label="Lancer la recherche">
            
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
     <script src="js/script.js" defer></Script>
    <script src="js/completion.js" defer></script>
</body>
</html>