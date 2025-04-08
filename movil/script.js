// Global variables to cache configuration and sections
let appConfig = null;
let sectionsData = null;
let currentUser = null;

// When the DOM is ready, check login status and set up event listeners
document.addEventListener('DOMContentLoaded', () => {
  // Check if user is already logged in
  checkLoginStatus();
  
  // Set up login form submission handler
  document.getElementById('loginForm').addEventListener('submit', handleLogin);
  
  // Attach listener to Back button
  document.getElementById('backButton').addEventListener('click', showMainScreen);
});

// Check if user is already logged in via localStorage
function checkLoginStatus() {
  const storedUser = localStorage.getItem('user');
  
  if (storedUser) {
    try {
      currentUser = JSON.parse(storedUser);
      // Load application if user is logged in
      loadAppData();
    } catch (e) {
      console.error("Error parsing stored user data:", e);
      showLoginScreen();
    }
  } else {
    // If no stored user, show login screen
    showLoginScreen();
  }
}

// Handle login form submission
function handleLogin(event) {
  event.preventDefault();
  
  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value;
  
  if (!username || !password) {
    showLoginMessage('Please enter username and password', 'error');
    return;
  }
  
  // Show loading message
  showLoginMessage('Logging in...', 'info');
  
  // Send credentials to login.php
  fetch('login.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ username, password })
  })
  .then(response => {
    if (!response.ok) throw new Error('Login request failed');
    return response.json();
  })
  .then(data => {
    if (data.success) {
      // Store user info in localStorage
      currentUser = { username, loginTime: new Date().toISOString() };
      localStorage.setItem('user', JSON.stringify(currentUser));
      
      // Load application data and show app
      loadAppData();
    } else {
      showLoginMessage('Login failed. Please try again.', 'error');
    }
  })
  .catch(error => {
    console.error('Login error:', error);
    showLoginMessage('Server error. Please try again later.', 'error');
  });
}

// Display login message
function showLoginMessage(message, type = 'info') {
  const messageElement = document.getElementById('loginMessage');
  messageElement.textContent = message;
  messageElement.className = `message ${type}`;
}

// Load application configuration and sections data
function loadAppData() {
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
    showAppScreen(); // Show the app screen after data is loaded
  })
  .catch(err => {
    console.error(err);
    showLoginMessage('Failed to load application data', 'error');
    // Show login screen again if app data fails to load
    showLoginScreen();
  });
}

// Show login screen and hide app
function showLoginScreen() {
  document.getElementById('loginScreen').classList.remove('hidden');
  document.getElementById('appContainer').classList.add('hidden');
  // Clear login form
  document.getElementById('username').value = '';
  document.getElementById('password').value = '';
  document.getElementById('loginMessage').textContent = '';
}

// Show app and hide login screen
function showAppScreen() {
  document.getElementById('loginScreen').classList.add('hidden');
  document.getElementById('appContainer').classList.remove('hidden');
}

// Handle logout
function logout() {
  // Clear localStorage
  localStorage.removeItem('user');
  currentUser = null;
  
  // Reset app state
  document.getElementById('mainScreen').classList.remove('hidden');
  document.getElementById('contentScreen').classList.add('hidden');
  
  // Show login screen
  showLoginScreen();
}

// Initialize the app based on configuration and sections JSON
function initializeApp() {
  // Set the header text dynamically and add logout button
  const headerEl = document.getElementById('appHeader');
  headerEl.innerHTML = `
    <h1>${appConfig.headerTitle}</h1>
    <button id="logoutButton">Logout</button>
  `;
  
  // Add event listener to logout button
  document.getElementById('logoutButton').addEventListener('click', logout);

  // Set the main welcome message with personalized greeting
  const mainScreen = document.getElementById('mainScreen');
  mainScreen.innerHTML = `
    <p>Hello, ${currentUser.username}!</p>
    <p>${appConfig.welcomeMessage}</p>
  `;

  // Build the bottom navigation dynamically using sections data
  const bottomNav = document.getElementById('bottomNav');
  bottomNav.innerHTML = ''; // Clear any existing content
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