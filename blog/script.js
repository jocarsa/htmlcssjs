// Global state variables
const postsPerPage = 6;
let allPosts = [];
let filteredPosts = [];
let currentPage = 1;
let currentCategory = "all";
let commentsStore = {};

// On DOM load, fetch JSON data and initialize
document.addEventListener('DOMContentLoaded', () => {
  fetch('data.json')
    .then(response => response.json())
    .then(data => {
      // Update header and footer from blog info in JSON
      if (data.blog) {
        document.querySelector('.blog-title').innerText = data.blog.title;
        document.querySelector('.blog-subtitle').innerText = data.blog.subtitle;
        document.querySelector('.footer-text').innerText = data.blog.footer;
      }
      // Dynamically load categories from JSON; fallback defaults if missing
      if (data.categories && Array.isArray(data.categories)) {
        renderCategories(data.categories);
      } else {
        renderCategories(["tech", "lifestyle", "news"]);
      }
      // Load posts and comments data
      allPosts = data.posts;
      commentsStore = data.comments || {};
      filteredPosts = [...allPosts];
      renderPosts();
      renderPagination();
    })
    .catch(err => console.error('Error loading posts:', err));

  // Event delegation for category clicks
  document.querySelector('.categories').addEventListener('click', (e) => {
    if (e.target && e.target.tagName === 'LI') {
      // Set active class on the selected category
      document.querySelectorAll('.categories li').forEach(li => li.classList.remove('active'));
      e.target.classList.add('active');
      currentCategory = e.target.dataset.category;
      currentPage = 1;
      filteredPosts = (currentCategory === "all")
        ? [...allPosts]
        : allPosts.filter(post => post.category === currentCategory);
      renderPosts();
      renderPagination();
    }
  });
});

// Render category list dynamically into the nav
function renderCategories(categories) {
  const categoriesContainer = document.querySelector('.categories');
  categoriesContainer.innerHTML = '';
  // Add the "All" option first
  const allItem = document.createElement('li');
  allItem.innerText = 'All';
  allItem.dataset.category = 'all';
  allItem.classList.add('active');
  categoriesContainer.appendChild(allItem);

  categories.forEach(category => {
    const li = document.createElement('li');
    li.innerText = category.charAt(0).toUpperCase() + category.slice(1);
    li.dataset.category = category;
    categoriesContainer.appendChild(li);
  });
}

// Render posts for the current page and filter criteria
function renderPosts() {
  const postsList = document.querySelector('.posts-list');
  postsList.innerHTML = '';
  // Ensure main view is visible
  postsList.style.display = 'grid';
  document.querySelector('.pagination').style.display = 'block';
  document.querySelector('.post-detail').classList.remove('active');

  let start = (currentPage - 1) * postsPerPage;
  let postsForPage = filteredPosts.slice(start, start + postsPerPage);

  postsForPage.forEach(post => {
    const card = document.createElement('div');
    card.classList.add('post-card');
    card.innerHTML = `
      <h2>${post.title}</h2>
      <p>${post.excerpt}</p>
      <p class="post-date">${post.date}</p>
    `;
    // Clicking a post card shows the detailed view
    card.addEventListener('click', () => {
      renderPostDetail(post);
    });
    postsList.appendChild(card);
  });
}

// Render pagination buttons based on filtered posts
function renderPagination() {
  const paginationContainer = document.querySelector('.pagination');
  paginationContainer.innerHTML = '';

  let totalPages = Math.ceil(filteredPosts.length / postsPerPage);
  for (let i = 1; i <= totalPages; i++) {
    const btn = document.createElement('button');
    btn.innerText = i;
    if (i === currentPage) btn.classList.add('active');
    btn.addEventListener('click', () => {
      currentPage = i;
      renderPosts();
      renderPagination();
      document.querySelector('.posts-list').scrollIntoView({ behavior: 'smooth' });
    });
    paginationContainer.appendChild(btn);
  }
}

// Render a full post view and hide the main posts list and pagination
function renderPostDetail(post) {
  // Hide main posts list and pagination
  document.querySelector('.posts-list').style.display = 'none';
  document.querySelector('.pagination').style.display = 'none';

  const postDetailSection = document.querySelector('.post-detail');
  postDetailSection.innerHTML = '';

  // Back button to return to main posts
  const backBtn = document.createElement('button');
  backBtn.innerText = 'Back to Posts';
  backBtn.classList.add('back-btn');
  backBtn.addEventListener('click', () => {
    postDetailSection.classList.remove('active');
    document.querySelector('.posts-list').style.display = 'grid';
    document.querySelector('.pagination').style.display = 'block';
  });
  postDetailSection.appendChild(backBtn);

  // Post title, date, and content
  const titleEl = document.createElement('h2');
  titleEl.innerText = post.title;
  postDetailSection.appendChild(titleEl);

  const dateEl = document.createElement('p');
  dateEl.classList.add('post-date');
  dateEl.innerText = post.date;
  postDetailSection.appendChild(dateEl);

  const contentEl = document.createElement('div');
  contentEl.innerHTML = post.content;
  postDetailSection.appendChild(contentEl);

  // Comment section (list + form)
  const commentSection = document.createElement('div');
  commentSection.classList.add('comment-section');
  const commentTitle = document.createElement('h3');
  commentTitle.innerText = "Comments";
  commentSection.appendChild(commentTitle);

  // Existing comments
  const commentList = document.createElement('div');
  commentList.classList.add('comments-list');
  const postComments = commentsStore[post.id] || [];
  postComments.forEach(comment => {
    const commentEl = document.createElement('div');
    commentEl.classList.add('comment');
    commentEl.innerHTML = `<strong>${comment.name}</strong>: ${comment.comment} <em>(${comment.date})</em>`;
    commentList.appendChild(commentEl);
  });
  commentSection.appendChild(commentList);

  // Comment form to add new comment
  const commentForm = document.createElement('form');
  commentForm.classList.add('comment-form');
  commentForm.innerHTML = `
    <input type="text" name="name" placeholder="Your name" required>
    <input type="email" name="email" placeholder="Your email" required>
    <textarea name="comment" placeholder="Your comment" required></textarea>
    <input type="submit" value="Submit Comment">
  `;
  commentForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(commentForm);
    const newComment = {
      name: formData.get('name'),
      comment: formData.get('comment'),
      date: new Date().toLocaleDateString()
    };
    if (!commentsStore[post.id]) {
      commentsStore[post.id] = [];
    }
    commentsStore[post.id].push(newComment);
    // Re-render the post detail to update comments
    renderPostDetail(post);
  });
  commentSection.appendChild(commentForm);

  postDetailSection.appendChild(commentSection);
  postDetailSection.classList.add('active');
  postDetailSection.scrollIntoView({ behavior: 'smooth' });
}
