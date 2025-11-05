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
  <nav class="nav-secondary">
    <button id="theme-toggle" type="button" class="nav-link" aria-label="Basculer le thème">
      <span id="theme-icon" aria-hidden="true">🐣</span>
    </button>
  </nav>

  <section class="search-main">
    <div class="search-container">
      <h1 class="logo-title">
        <span class="logo-icon" aria-hidden="true">🔎</span>
        Le dico des animaux
      </h1>

      <form class="search-form" action="recherche.php" method="get" role="search">
        <div class="search-wrapper">
          <label for="search-input" class="sr-only">Rechercher un animal</label>

          <input
            id="search-input"
            name="search"
            class="search-input"
            type="search"
            placeholder="Rechercher un animal…"
            autocomplete="off"
            role="combobox"
            aria-autocomplete="list"
            aria-expanded="false"
            aria-owns="suggestions-list"
            aria-controls="suggestions-list"
          />

          <button class="search-button" type="submit" aria-label="Lancer la recherche">🔍</button>

          <div id="search-loading" class="search-loading" aria-hidden="true" aria-live="polite">
            <div class="loading-spinner"></div>
          </div>
        </div>

        <div id="suggestions-container" class="suggestions-container" role="listbox" aria-label="Suggestions de recherche">
          <ul id="suggestions-list" class="suggestions-list"></ul>
        </div>
      </form>
    </div>
  </section>
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
                            aria-autocomplete="list"
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
     <script src="js/script.js" defer></Script>
    <script src="js/completion.js" defer></script>
</body>
</html>