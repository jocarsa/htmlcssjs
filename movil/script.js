// Global variables to cache configuration and sections
let appConfig = null;
let sectionsData = null;

// When the DOM is ready, load both JSON files
document.addEventListener('DOMContentLoaded', () => {
  Promise.all([
    fetch('config.json').then(response => {
      if (!response.ok) throw new Error("Failed to load config.json");
      return response.json();
    }),
    fetch('sections.json').then(response => {
      if (!response.ok) throw new Error("Failed to load sections.json");
      return response.json();
    })
  ])
  .then(([config, sections]) => {
    appConfig = config;
    sectionsData = sections;
    initializeApp();
  })
  .catch(err => {
    console.error(err);
  });

  // Attach listener to Back button (exists in HTML)
  document.getElementById('backButton').addEventListener('click', showMainScreen);
});

// Initialize the app based on configuration and sections JSON
function initializeApp() {
  // Set the header text dynamically
  const headerEl = document.getElementById('appHeader');
  headerEl.innerHTML = `<h1>${appConfig.headerTitle}</h1>`;

  // Set the main welcome message dynamically
  const mainScreen = document.getElementById('mainScreen');
  mainScreen.innerHTML = `<p>${appConfig.welcomeMessage}</p>`;

  // Build the bottom navigation dynamically using sections data
  // Here, we display only the icon from each section
  const bottomNav = document.getElementById('bottomNav');
  let ul = document.createElement('ul');
  sectionsData.sections.forEach(section => {
    let li = document.createElement('li');
    li.setAttribute('data-target', section.id);
    li.innerHTML = section.icon; // Only the icon is shown on the nav bar
    li.addEventListener('click', () => loadScreen(section.id));
    ul.appendChild(li);
  });
  bottomNav.appendChild(ul);
}

// Load a screen by section id and render its content dynamically
function loadScreen(screenId) {
  if (!sectionsData) {
    console.error("Sections data not loaded!");
    return;
  }
  const section = sectionsData.sections.find(sec => sec.id === screenId);
  if (!section) {
    console.error(`Section with id "${screenId}" not found.`);
    return;
  }
  
  // Build the content using the section data with both icon and title
  let html = `<h2>${section.icon} ${section.title}</h2>`;
  
  // Handle the case where the content is a string or an array of articles
  if (typeof section.content === 'string') {
    html += `<p>${section.content}</p>`;
  } else if (Array.isArray(section.content)) {
    section.content.forEach(article => {
      html += `
        <article>
          <h3>${article.header}</h3>
          <p>${article.body}</p>
        </article>
      `;
    });
  } else {
    html += "<p>Error: Unexpected content format.</p>";
  }
  
  document.getElementById('contentContainer').innerHTML = html;

  // Toggle screen visibility: hide main screen, show content screen
  document.getElementById('mainScreen').classList.add('hidden');
  document.getElementById('contentScreen').classList.remove('hidden');
}

// Show the main screen and hide the content screen
function showMainScreen() {
  document.getElementById('mainScreen').classList.remove('hidden');
  document.getElementById('contentScreen').classList.add('hidden');
}
