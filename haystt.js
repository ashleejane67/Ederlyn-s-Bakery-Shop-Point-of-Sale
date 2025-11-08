let orderItems = {};
let total = 0;
let salesData = [];
let totalSales = 0;
let totalAmount = 0;
let currentCustomerId = 1; 
let isLoggedIn = false;
let customerCounter = 1; 
let inventoryItems = [
    { name: "Hopia", stock: "20pcs", status: "In Stock" },
    { name: "Monay", stock: "15pcs", status: "In Stock" },
    { name: "Ensaymada", stock: "8pcs", status: "Low Stock" },
    { name: "Pan de Coco", stock: "0pcs", status: "Out of Stock" },
    { name: "Torta", stock: "0pcs", status: "Out of Stock" },
    { name: "Slice Bread", stock: "0pcs", status: "Out of Stock" },
    { name: "Spanish Bread", stock: "0pcs", status: "Out of Stock" }
];

function updateOrderDisplay() {
    const orderItemsContainer = document.getElementById('orderItems');
    const payableAmount = document.getElementById('payableAmount');
    orderItemsContainer.innerHTML = '';
    if (Object.keys(orderItems).length === 0) {
        orderItemsContainer.innerHTML = '<div id="no-items-message">No items in your order yet</div>';
        payableAmount.textContent = '₱0.00';
        return;
    }
    total = 0;
    for (const [itemName, item] of Object.entries(orderItems)) {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        const itemElement = document.createElement('div');
        itemElement.className = 'order-item';
        itemElement.innerHTML = `
            <img src="${item.image}" alt="${itemName}" class="order-item-image">
            <div class="order-item-details">
                <div class="order-item-name">${itemName}</div>
                <div class="order-item-price">₱${item.price.toFixed(2)}</div>
            </div>
            <div class="order-item-quantity">
                <div class="quantity-btn" data-name="${itemName}" data-action="decrease">-</div>
                <div class="quantity-value">${item.quantity}</div>
                <div class="quantity-btn" data-name="${itemName}" data-action="increase">+</div>
            </div>
            <button class="remove-btn" data-name="${itemName}">Remove</button>
        `;
        orderItemsContainer.appendChild(itemElement);
    }
    payableAmount.textContent = `₱${total.toFixed(2)}`;
    document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', handleQuantityChange);
    });
    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', handleRemoveItem);
    });
}

function handleQuantityChange(event) {
    const itemName = event.target.dataset.name;
    const action = event.target.dataset.action;
    if (action === 'increase') {
        orderItems[itemName].quantity++;
    } else if (action === 'decrease' && orderItems[itemName].quantity > 1) {
        orderItems[itemName].quantity--;
    }
    updateOrderDisplay();
}

function handleRemoveItem(event) {
    const itemName = event.target.dataset.name;
    delete orderItems[itemName];
    updateOrderDisplay();
}

function updateInventoryOnSale() {
    Object.keys(orderItems).forEach(itemName => {
        const quantitySold = orderItems[itemName].quantity;
        const inventoryItem = inventoryItems.find(item => 
            item.name.toLowerCase() === itemName.toLowerCase()
        );
        if (inventoryItem) {
            const stockMatch = inventoryItem.stock.match(/\d+/);
            if (stockMatch) {
                const currentStock = parseInt(stockMatch[0]);
                const newStock = Math.max(0, currentStock - quantitySold);
                const unit = inventoryItem.stock.replace(stockMatch[0], '');
                inventoryItem.stock = `${newStock}${unit}`;
                inventoryItem.status = determineStatus(inventoryItem.stock);
            }
        }
    });
    saveInventoryData();
    updateInventoryTable();
    checkStockAlerts();
}

function checkStockAlerts() {
    const lowStockProducts = inventoryItems.filter(item => 
        item.status === "Low Stock" || item.status === "Out of Stock"
    );
    if (lowStockProducts.length > 0) {
        let alertMessage = "";
        let alertClass = "";
        if (lowStockProducts.some(item => item.status === "Out of Stock")) {
            alertMessage = "Some products are out of stock!";
            alertClass = "alert-danger";
        } else {
            alertMessage = "Some products are low in stock!";
            alertClass = "alert-warning";
        }
        showAlert(alertMessage, alertClass);
    }
    updateDashboardStats();
}

function showAlert(message, alertClass) {
    const alertBox = document.getElementById('alertBox');
    alertBox.textContent = message;
    alertBox.className = `alert-box ${alertClass}`;
    alertBox.style.display = 'block';
    setTimeout(() => {
        alertBox.style.display = 'none';
    }, 5000);
}

function determineStatus(stock) {
    if (!stock) return "Out of Stock";
    const numericValue = parseFloat(stock);
    if (isNaN(numericValue)) return "Out of Stock";
    if (numericValue <= 0) return "Out of Stock";
    if (numericValue < 10) return "Low Stock";
    return "In Stock";
}

function saveOrderToSales() {
    if (Object.keys(orderItems).length === 0) return false;
    const now = new Date();
    const date = `${now.getMonth() + 1}/${now.getDate()}/${now.getFullYear()}`;
    const time = `${now.getHours()}:${now.getMinutes().toString().padStart(2, '0')}`;
    const customerId = currentCustomerId;
    document.getElementById('customerIdDisplay').textContent = `Customer #${customerId}`;
    const sale = {
        date: `${date} ${time}`,
        customer: customerId,
        amount: total,
        salesBy: document.getElementById('operatorName').textContent
    };
    salesData.unshift(sale);
    totalSales++;
    totalAmount += total;
    customerCounter++;
    currentCustomerId = customerCounter;
    localStorage.setItem('salesData', JSON.stringify(salesData));
    localStorage.setItem('totalSales', totalSales);
    localStorage.setItem('totalAmount', totalAmount);
    localStorage.setItem('customerCounter', customerCounter);
    updateSalesReport();
    return true;
}

function updateSalesReport() {
    const salesTableBody = document.getElementById('salesTableBody');
    salesTableBody.innerHTML = '';
    if (salesData.length === 0) {
        salesTableBody.innerHTML = '<tr><td colspan="4">No sales data available</td></tr>';
        return;
    }
    const recentSales = salesData.slice(0, 10);
    recentSales.forEach(sale => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${sale.date}</td>
            <td>Customer #${sale.customer}</td>
            <td>₱${sale.amount.toFixed(2)}</td>
            <td>${sale.salesBy}</td>
        `;
        salesTableBody.appendChild(row);
    });
}

function loadSalesData() {
    const savedSalesData = localStorage.getItem('salesData');
    const savedTotalSales = localStorage.getItem('totalSales');
    const savedTotalAmount = localStorage.getItem('totalAmount');
    const savedCounter = localStorage.getItem('customerCounter');
    if (savedSalesData) {
        salesData = JSON.parse(savedSalesData);
    }
    if (savedTotalSales) {
        totalSales = parseInt(savedTotalSales);
    }
    if (savedTotalAmount) {
        totalAmount = parseFloat(savedTotalAmount);
    }
    if (savedCounter) {
        customerCounter = parseInt(savedCounter);
        currentCustomerId = customerCounter;
    } else {
        if (salesData.length > 0) {
            customerCounter = Math.max(...salesData.map(sale => sale.customer)) + 1;
            currentCustomerId = customerCounter;
        } else {
            customerCounter = 1;
            currentCustomerId = 1;
        }
    }
    document.getElementById('customerIdDisplay').textContent = `Customer #${currentCustomerId}`;
    updateSalesReport();
}

function updateDashboardStats() {
    let outOfStockCount = 0;
    let lowStockCount = 0;
    let totalProducts = 0;
    inventoryItems.forEach(item => {
        if (item.status === "Out of Stock") outOfStockCount++;
        if (item.status === "Low Stock") lowStockCount++;
        totalProducts++;
    });
    document.getElementById('totalSaleAmount').textContent = `₱${totalAmount.toFixed(2)}`;
    document.getElementById('totalSaleCount').textContent = totalSales;
    document.getElementById('totalProducts').textContent = totalProducts;
    document.getElementById('lowStockCount').textContent = `Low Stock: ${lowStockCount}`;
    const lowStockElement = document.getElementById('lowStockCount');
    if (lowStockCount > 0) {
        lowStockElement.style.color = "#ff9800";
        lowStockElement.style.fontWeight = "bold";
    } else {
        lowStockElement.style.color = "";
        lowStockElement.style.fontWeight = "";
    }
}

function loadInventoryData() {
    const savedInventory = localStorage.getItem('inventoryItems');
    if (savedInventory) {
        inventoryItems = JSON.parse(savedInventory);
    }
    updateInventoryTable();
}

function saveInventoryData() {
    localStorage.setItem('inventoryItems', JSON.stringify(inventoryItems));
}

function updateInventoryTable() {
    const tableBody = document.getElementById('inventoryTableBody');
    tableBody.innerHTML = '';
    inventoryItems.forEach((item, index) => {
        const row = document.createElement('tr');
        let statusClass = 'in-stock';
        if (item.status === 'Low Stock') {
            statusClass = 'low-stock';
        } else if (item.status === 'Out of Stock') {
            statusClass = 'out-of-stock';
        }
        row.innerHTML = `
            <td>${item.name}</td>
            <td>${item.stock}</td>
            <td><span class="status ${statusClass}">${item.status}</span></td>
            <td>
                <button class="update-btn" data-index="${index}">Update</button>
                <button class="delete-btn" data-index="${index}">Delete</button>
            </td>
        `;
        tableBody.appendChild(row);
    });
    document.querySelectorAll('.update-btn').forEach(button => {
        button.addEventListener('click', function() {
            openUpdateModal(parseInt(this.dataset.index));
        });
    });
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            deleteInventoryItem(parseInt(this.dataset.index));
        });
    });
}

function openAddModal() {
    const modal = document.getElementById('addItemModal');
    modal.style.display = 'block';
    document.getElementById('addItemForm').reset();
}

function openUpdateModal(index) {
    const modal = document.getElementById('updateItemModal');
    const item = inventoryItems[index];
    document.getElementById('updateItemIndex').value = index;
    document.getElementById('updateItemName').value = item.name;
    document.getElementById('updateItemStock').value = item.stock;
    modal.style.display = 'block';
}

function closeModals() {
    document.getElementById('addItemModal').style.display = 'none';
    document.getElementById('updateItemModal').style.display = 'none';
}

function addInventoryItem(event) {
    event.preventDefault();
    const name = document.getElementById('itemName').value;
    const stock = document.getElementById('itemStock').value;
    const status = determineStatus(stock);
    inventoryItems.push({
        name: name,
        stock: stock,
        status: status
    });
    saveInventoryData();
    updateInventoryTable();
    closeModals();
    checkStockAlerts();
}

function updateInventoryItem(event) {
    event.preventDefault();
    const index = document.getElementById('updateItemIndex').value;
    const name = document.getElementById('updateItemName').value;
    const stock = document.getElementById('updateItemStock').value;
    const status = determineStatus(stock);
    inventoryItems[index] = {
        name: name,
        stock: stock,
        status: status
    };
    saveInventoryData();
    updateInventoryTable();
    closeModals();
    checkStockAlerts();
}

function deleteInventoryItem(index) {
    if (confirm('Are you sure you want to delete this product?')) {
        inventoryItems.splice(index, 1);
        saveInventoryData();
        updateInventoryTable();
        checkStockAlerts();
    }
}

function showLoginPage() {
    document.getElementById('loginPage').style.display = 'flex';
    document.getElementById('appContent').style.display = 'none';
    isLoggedIn = false;
}

function showDashboard() {
    if (!isLoggedIn) {
        showLoginPage();
        return;
    }
    document.getElementById('dashboardContent').style.display = 'block';
    document.getElementById('orderContent').style.display = 'none';
    document.getElementById('inventoryContent').style.display = 'none';
    document.getElementById('dashboardNav').classList.add('active');
    document.getElementById('orderNav').classList.remove('active');
    document.getElementById('inventoryNav').classList.remove('active');
    document.getElementById('logoutNav').classList.remove('active');
    updateSalesReport();
    updateDashboardStats();
}

function showOrder() {
    if (!isLoggedIn) {
        showLoginPage();
        return;
    }
    document.getElementById('dashboardContent').style.display = 'none';
    document.getElementById('orderContent').style.display = 'flex';
    document.getElementById('inventoryContent').style.display = 'none';
    document.getElementById('dashboardNav').classList.remove('active');
    document.getElementById('orderNav').classList.add('active');
    document.getElementById('inventoryNav').classList.remove('active');
}

function showInventory() {
    if (!isLoggedIn) {
        showLoginPage();
        return;
    }
    document.getElementById('dashboardContent').style.display = 'none';
    document.getElementById('orderContent').style.display = 'none';
    document.getElementById('inventoryContent').style.display = 'block';
    document.getElementById('dashboardNav').classList.remove('active');
    document.getElementById('orderNav').classList.remove('active');
    document.getElementById('inventoryNav').classList.add('active');
    updateInventoryTable();
}

function showMainApp() {
    document.getElementById('loginPage').style.display = 'none';
    document.getElementById('appContent').style.display = 'flex';
    isLoggedIn = true;
    showDashboard();
}

document.addEventListener('DOMContentLoaded', function() {
    loadSalesData();
    loadInventoryData();
    checkStockAlerts();
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();
            let username = document.getElementById('username').value;
            let password = document.getElementById('password').value;
            const messageBox = document.getElementById('messageBox');
            if (username === "admin" && password === "admin123") {
                messageBox.style.backgroundColor = "#d4edda";
                messageBox.style.color = "#155724";
                messageBox.textContent = "Login successful!";
                document.getElementById('operatorName').textContent = "OWNER";
                setTimeout(() => showMainApp(), 1000);
            } else {
                messageBox.style.backgroundColor = "#f8d7da";
                messageBox.style.color = "#721c24";
                messageBox.textContent = "Invalid username or password!";
            }
        });
    }
    const dashboardNav = document.getElementById('dashboardNav');
    if (dashboardNav) dashboardNav.addEventListener('click', showDashboard);
    const orderNav = document.getElementById('orderNav');
    if (orderNav) orderNav.addEventListener('click', showOrder);
    const inventoryNav = document.getElementById('inventoryNav');
    if (inventoryNav) inventoryNav.addEventListener('click', showInventory);
    const logoutNav = document.getElementById('logoutNav');
    if (logoutNav) {
        logoutNav.addEventListener('click', function() {
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
            document.getElementById('messageBox').textContent = '';
            orderItems = {};
            updateOrderDisplay();
            showLoginPage();
        });
    }
    document.querySelectorAll('.product-card:not(.add-stock)').forEach(card => {
        card.addEventListener('click', function() {
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);
            const image = this.dataset.image;
            if (orderItems[name]) {
                orderItems[name].quantity++;
            } else {
                orderItems[name] = {
                    price: price,
                    quantity: 1,
                    image: image
                };
            }
            updateOrderDisplay();
        });
    });
    const addStockBtn = document.querySelector('.product-card.add-stock');
    if (addStockBtn) {
        addStockBtn.addEventListener('click', function() {
            showInventory();
        });
    }
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', function() {
            if (Object.keys(orderItems).length === 0) {
                alert('No items to place order!');
                return;
            }
            const payableAmount = parseFloat(document.getElementById('payableAmount').textContent.replace('₱', ''));
            const paymentInput = document.getElementById('paymentInput');
            const paymentValue = paymentInput.value ? parseFloat(paymentInput.value) : 0;
            if (paymentValue < payableAmount) {
                alert('Insufficient payment amount!');
                return;
            }
            if (confirm('Complete this order?')) {
                try {
                    updateInventoryOnSale();
                    const success = saveOrderToSales();
                    if (success) {
                        const change = paymentValue - payableAmount;
                        alert(`Order placed successfully!
Total: ₱${payableAmount.toFixed(2)}
Received: ₱${paymentValue.toFixed(2)}
Change: ₱${change.toFixed(2)}`);
                        orderItems = {};
                        paymentInput.value = '';
                        document.getElementById('changeAmount').textContent = '₱0.00';
                        updateOrderDisplay();
                    }
                } catch (error) {
                    alert('Error placing order: ' + error.message);
                }
            }
        });
    }
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            if (this.closest('#inventoryContent')) {
                document.querySelectorAll('#inventoryTableBody tr').forEach(row => {
                    const productName = row.cells[0].textContent.toLowerCase();
                    if (productName.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            } else {
                document.querySelectorAll('.product-card:not(.add-stock)').forEach(card => {
                    const productName = card.dataset.name.toLowerCase();
                    card.style.display = productName.includes(searchTerm) ? 'block' : 'none';
                });
            }
        });
    });
    const paymentInput = document.getElementById('paymentInput');
    if (paymentInput) {
        paymentInput.addEventListener('input', function() {
            const payableAmount = parseFloat(document.getElementById('payableAmount').textContent.replace('₱', '')) || 0;
            const paymentValue = parseFloat(this.value) || 0;
            const changeAmount = document.getElementById('changeAmount');
            const change = paymentValue - payableAmount;
            if (change >= 0) {
                changeAmount.textContent = `₱${change.toFixed(2)}`;
                changeAmount.style.color = '#4a90e2';
            } else {
                changeAmount.textContent = `₱${Math.abs(change).toFixed(2)} short`;
                changeAmount.style.color = '#ff6b6b';
            }
        });
    }
    const addItemBtn = document.getElementById('addItemBtn');
    if (addItemBtn) {
        addItemBtn.addEventListener('click', openAddModal);
    }
    const addItemForm = document.getElementById('addItemForm');
    if (addItemForm) {
        addItemForm.addEventListener('submit', addInventoryItem);
    }
    const updateItemForm = document.getElementById('updateItemForm');
    if (updateItemForm) {
        updateItemForm.addEventListener('submit', updateInventoryItem);
    }
    document.querySelectorAll('.close').forEach(closeBtn => {
        closeBtn.addEventListener('click', closeModals);
    });
    window.addEventListener('click', function(event) {
        const addItemModal = document.getElementById('addItemModal');
        const updateItemModal = document.getElementById('updateItemModal');
        if ((addItemModal && event.target === addItemModal) || 
            (updateItemModal && event.target === updateItemModal)) {
            closeModals();
        }
    });
    showLoginPage();
});
