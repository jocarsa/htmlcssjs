document.addEventListener("DOMContentLoaded", () => {
  // Global variables to store fetched products and cart items
  let products = [];
  let cart = [];

  // Cache DOM elements for later use
  const homeView = document.getElementById("home-view");
  const productView = document.getElementById("product-view");
  const checkoutView = document.getElementById("checkout-view");
  const productsGrid = document.getElementById("products-grid");
  const productDetails = document.getElementById("product-details");
  const checkoutItems = document.getElementById("checkout-items");
  const cartCountElement = document.getElementById("cart-count");
  const orderConfirmation = document.getElementById("order-confirmation");

  // Update the cart count in the header
  function updateCartCount() {
    const totalCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCountElement.textContent = totalCount;
  }

  // Load products from the JSON file
  function loadProducts() {
    fetch('products.json')
      .then(response => response.json())
      .then(data => {
        products = data.products;
        renderProductsGrid();
      })
      .catch(error => console.error('Error loading products:', error));
  }

  // Render the products grid using fetched products
  function renderProductsGrid() {
    productsGrid.innerHTML = "";
    products.forEach(product => {
      const card = document.createElement("div");
      card.className = "product-card";
      card.innerHTML = `
        <img src="${product.image}" alt="${product.name}">
        <h3>${product.name}</h3>
        <p>$${product.price.toFixed(2)}</p>
      `;
      card.addEventListener("click", () => {
        window.location.hash = `#product?id=${product.id}`;
      });
      productsGrid.appendChild(card);
    });
  }

  // Utility to control which view is visible
  function showView(view) {
    homeView.style.display = view === 'home' ? 'block' : 'none';
    productView.style.display = view === 'product' ? 'block' : 'none';
    checkoutView.style.display = view === 'checkout' ? 'block' : 'none';
  }

  // Render the details for a selected product
  function renderProductDetails(productId) {
    const product = products.find(p => p.id === parseInt(productId));
    if (!product) {
      productDetails.innerHTML = "<p>Product not found.</p>";
      return;
    }
    productDetails.innerHTML = `
      <img src="${product.image}" alt="${product.name}">
      <h2>${product.name}</h2>
      <p>${product.description}</p>
      <p>Price: $${product.price.toFixed(2)}</p>
      <label for="quantity">Quantity: </label>
      <input type="number" id="quantity" value="1" min="1">
      <button id="add-to-cart">Add to Cart</button>
    `;
    
    document.getElementById("add-to-cart").addEventListener("click", () => {
      const quantity = parseInt(document.getElementById("quantity").value);
      addToCart(product, quantity);
      alert(`${product.name} added to cart.`);
      updateCartCount();
    });
  }

  // Add the product to the cart (or update the quantity)
  function addToCart(product, quantity) {
    const existingItem = cart.find(item => item.product.id === product.id);
    if (existingItem) {
      existingItem.quantity += quantity;
    } else {
      cart.push({ product, quantity });
    }
  }

  // Render the checkout view with the cart content and total
  function renderCheckout() {
    checkoutItems.innerHTML = "";
    if (cart.length === 0) {
      checkoutItems.innerHTML = "<p>Your cart is empty.</p>";
      return;
    }
    const ul = document.createElement("ul");
    cart.forEach(item => {
      const li = document.createElement("li");
      li.textContent = `${item.product.name} - ${item.quantity} x $${item.product.price.toFixed(2)} = $${(item.product.price * item.quantity).toFixed(2)}`;
      ul.appendChild(li);
    });
    const total = cart.reduce((sum, item) => sum + item.product.price * item.quantity, 0);
    const totalDiv = document.createElement("div");
    totalDiv.className = "checkout-total";
    totalDiv.textContent = `Total: $${total.toFixed(2)}`;
    checkoutItems.appendChild(ul);
    checkoutItems.appendChild(totalDiv);
  }

  // Event listener for order confirmation
  document.getElementById("confirm-order").addEventListener("click", () => {
    if (cart.length === 0) {
      alert("Your cart is empty.");
      return;
    }
    orderConfirmation.textContent = "Thank you for your order!";
    orderConfirmation.style.display = "block";
    // Clear the cart after confirming
    cart = [];
    updateCartCount();
  });

  // Continue shopping button returns the user to the home view
  document.getElementById("continue-shopping").addEventListener("click", () => {
    window.location.hash = "#home";
  });

  // Back button from the product details view
  document.getElementById("back-home").addEventListener("click", () => {
    window.location.hash = "#home";
  });

  // Router function to switch views based on the hash
  function router() {
    const hash = window.location.hash;
    if (!hash || hash === "#home") {
      showView("home");
    } else if (hash.startsWith("#product")) {
      showView("product");
      const urlParams = new URLSearchParams(hash.split('?')[1]);
      const id = urlParams.get("id");
      renderProductDetails(id);
    } else if (hash === "#checkout") {
      showView("checkout");
      orderConfirmation.style.display = "none"; // reset confirmation message
      renderCheckout();
    }
  }

  window.addEventListener("hashchange", router);
  router(); // initial routing

  // Load product data on start
  loadProducts();
});
