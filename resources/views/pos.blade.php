<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pro POS - Hardware Ready</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Barcode Library -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        :root { --pos-primary: #0d6efd; --pos-bg: #f4f6f9; }
        body { background-color: var(--pos-bg); overflow: hidden; height: 100vh; font-family: "Segoe UI", sans-serif; }
        .pos-wrapper { height: 100vh; display: flex; flex-direction: column; }
        .pos-main { flex: 1; overflow: hidden; display: flex; }
        .products-column { flex: 1; overflow-y: auto; padding: 15px; }
        .cart-column { width: 420px; background: white; border-left: 1px solid #dee2e6; display: flex; flex-direction: column; }
        .cart-items { flex: 1; overflow-y: auto; padding: 10px; background: #fff; }
        .product-card { cursor: pointer; border: none; border-radius: 12px; height: 100%; transition: 0.2s; position: relative; background: white; border: 1px solid #eee; }
        .product-card:hover { border-color: var(--pos-primary); transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .product-img { height: 100px; object-fit: cover; border-radius: 10px; }
        .stock-badge { position: absolute; top: 8px; right: 8px; font-size: 0.65rem; z-index: 10; border-radius: 20px; }
        .label-btn { position: absolute; bottom: 8px; right: 8px; z-index: 11; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; padding: 0; }

        @media print {
            body * { visibility: hidden; }
            #receiptPrintArea, #receiptPrintArea * { visibility: visible; }
            #receiptPrintArea { position: absolute; left: 0; top: 0; width: 100%; display: block !important; }
            .is-printing-label #receiptPrintArea { display: none !important; }
            .is-printing-label #labelPrintArea, .is-printing-label #labelPrintArea * { visibility: visible; }
            .is-printing-label #labelPrintArea { position: absolute; left: 0; top: 0; width: 50mm; height: 25mm; display: flex !important; flex-direction: column; align-items: center; justify-content: center; }
            @page { margin: 0; }
        }
    </style>
</head>
<body>

<div class="pos-wrapper">
    <nav class="navbar navbar-dark bg-dark px-3 shadow">
        <span class="navbar-brand fw-bold text-info"><i class="bi bi-upc-scan me-2"></i>RETAIL TERMINAL</span>
        <div class="ms-auto d-flex gap-2">
            <button class="btn btn-outline-info btn-sm rounded-pill px-3" onclick="showDailyReport()"><i class="bi bi-graph-up me-1"></i> Report</button>
            <button class="btn btn-outline-light btn-sm rounded-pill px-3" onclick="showPosOrders()"><i class="bi bi-clock-history me-1"></i> History</button>
            <button class="btn btn-danger btn-sm rounded-pill" onclick="logout()">Logout</button>
        </div>
    </nav>

    <div id="posSection" class="pos-main">
        <div class="products-column">
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white border">
                        <span class="input-group-text bg-white border-0"><i class="bi bi-search text-primary"></i></span>
                        <input type="text" id="searchInput" class="form-control border-0 py-2" placeholder="Scan Barcode or Type Name..." autofocus>
                    </div>
                </div>
                <div class="col-md-6 d-flex align-items-center overflow-auto no-scrollbar gap-2" id="categoryFilters">
                    <span class="badge rounded-pill bg-primary text-white p-2 px-3 cursor-pointer" onclick="filterCategory('', this)">All</span>
                </div>
            </div>
            <div id="productList" class="row g-2"></div>
            <div id="paginationNav" class="mt-4 d-flex justify-content-center"></div>
        </div>

        <div class="cart-column shadow-lg" id="cartColumn">
            <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center"><h6 class="mb-0 fw-bold">Cart</h6><button class="btn btn-sm btn-outline-danger" onclick="clearCart()"><i class="bi bi-trash"></i></button></div>
            <div id="cartItems" class="cart-items"></div>
            <div class="p-4 border-top">
                <div class="d-flex justify-content-between mb-1 small text-muted"><span>Subtotal</span><span id="subtotal">0.00</span></div>
                <div class="d-flex justify-content-between mb-3"><span class="h4 mb-0 fw-bold">TOTAL</span><span id="total" class="h4 mb-0 fw-bold text-primary">0.00</span></div>
                <button id="payBtn" class="btn btn-success w-100 py-3 rounded-4 fw-bold shadow" onclick="showCheckoutModal()" disabled>CHECKOUT (F9)</button>
            </div>
        </div>
    </div>
</div>

<!-- Price Label Modal (Hardware Test Ready) -->
<div class="modal fade" id="labelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0"><h6 class="modal-title fw-bold">Generate Price Sticker</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body text-center p-4">
                <div id="labelPreview" class="border p-3 mb-4 bg-white shadow-sm rounded mx-auto" style="width: 55mm; min-height: 30mm; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <div id="lpName" class="fw-bold small text-truncate w-100 mb-1"></div>
                    <h4 id="lpPrice" class="fw-bold text-primary mb-2"></h4>
                    <!-- Visual Barcode Lines -->
                    <svg id="barcode"></svg>
                </div>
                <button class="btn btn-dark w-100 rounded-pill py-2 fw-bold shadow" onclick="doPrintLabel()"><i class="bi bi-printer me-2"></i>PRINT TO ZEBRA</button>
                <p class="small text-muted mt-3">This will print scannable lines for your barcode machine.</p>
            </div>
        </div>
    </div>
</div>

<!-- (Checkout and Other Modals remain same...) -->
<div class="modal fade" id="checkoutModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 rounded-4 shadow-lg p-4"><h5 class="fw-bold mb-4">Finalize Sale</h5><input type="text" id="custName" class="form-control mb-3" value="Walk-in Customer"><label class="small fw-bold">Manual Discount ($)</label><input type="number" id="orderDiscount" class="form-control mb-4" value="0" oninput="calculateCheckoutTotal()"><div class="d-flex gap-2 mb-4"><input type="radio" class="btn-check" name="payMethod" id="payCod" value="cod" checked><label class="btn btn-outline-primary flex-fill p-3 fw-bold" for="payCod">CASH</label><input type="radio" class="btn-check" name="payMethod" id="payBank" value="bank_transfer"><label class="btn btn-outline-primary flex-fill p-3 fw-bold" for="payBank">CARD</label></div><div class="bg-primary text-white p-4 rounded-4 text-center shadow-inner"><span class="small opacity-75 fw-bold">FINAL AMOUNT DUE</span><h1 class="mb-0 fw-bold" id="finalTotal">0.00</h1></div><button class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-lg mt-4" id="confirmOrderBtn" onclick="submitOrder()">PLACE ORDER & PRINT INVOICE</button></div></div></div>
<div class="modal fade" id="reportModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 rounded-4 shadow"><div class="modal-header border-0 bg-info text-white rounded-top-4 p-4"><h5 class="modal-title fw-bold">Daily Sales Report</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body p-4 text-center"><h6 id="reportDate" class="text-muted mb-4"></h6><div class="row g-3"><div class="col-6"><div class="p-3 bg-light rounded-4"><h6>Orders</h6><h3 class="fw-bold mb-0" id="repCount">0</h3></div></div><div class="col-6"><div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4"><h6>Total</h6><h3 class="fw-bold mb-0" id="repTotal">$0</h3></div></div><div class="col-6"><div class="p-3 bg-success bg-opacity-10 text-success rounded-4"><h6>Cash</h6><h3 class="fw-bold mb-0" id="repCash">$0</h3></div></div><div class="col-6"><div class="p-3 bg-warning bg-opacity-10 text-warning rounded-4"><h6>Card</h6><h3 class="fw-bold mb-0" id="repCard">$0</h3></div></div></div></div></div></div></div>
<div class="modal fade" id="posOrdersModal" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content border-0 rounded-4 shadow"><div class="modal-header border-0 bg-dark text-white rounded-top-4 p-4"><h5 class="modal-title fw-bold">POS Order History</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body p-0"><table class="table table-hover mb-0"><thead class="table-light"><tr><th class="ps-4">ID</th><th>Date</th><th>Amount</th><th>Method</th><th class="text-end pe-4">Action</th></tr></thead><tbody id="posOrdersTable"></tbody></table></div></div></div></div>

<div id="receiptPrintArea" style="display:none; font-family: monospace;"></div>
<div id="labelPrintArea" style="display:none; font-family: sans-serif; text-align:center;">
    <div id="lName" style="font-size: 9pt; font-weight: bold;"></div>
    <div id="lPrice" style="font-size: 14pt; font-weight: bold; margin: 2px 0;"></div>
    <svg id="labelBarcode"></svg>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let apiToken = localStorage.getItem("pos_token");
    let currentCategory = ""; let cartGlobal = null;
    let reportModal, posOrdersModal, labelModal, checkoutModal;

    function checkAuth() { if (!apiToken) window.location.href = "/pos"; loadCategories(); loadProducts(); loadCart(); }
    function logout() { localStorage.removeItem("pos_token"); window.location.reload(); }

    async function loadCategories() {
        const res = await fetch("/api/categories"); const data = await res.json();
        const container = document.getElementById("categoryFilters");
        data.data.forEach(cat => {
            const span = document.createElement("span"); span.className = "badge rounded-pill bg-white text-dark border p-2 px-3 cursor-pointer";
            span.innerText = cat.name; span.onclick = (e) => filterCategory(cat.id, e.target);
            container.appendChild(span);
        });
    }
    function filterCategory(id, el) { 
        document.querySelectorAll("#categoryFilters .badge").forEach(b => { b.classList.remove("bg-primary", "text-white"); b.classList.add("bg-white", "text-dark", "border"); });
        el.classList.remove("bg-white", "text-dark", "border"); el.classList.add("bg-primary", "text-white");
        loadProducts(id);
    }

    async function loadProducts(catId = "", page = 1) {
        currentCategory = catId; const search = document.getElementById("searchInput").value;
        const res = await fetch(`/api/products?category_id=${catId}&search=${encodeURIComponent(search)}&page=${page}&per_page=12`);
        const json = await res.json();
        
        if (search && json.data.data.length === 1 && json.data.data[0].sku === search) { 
            document.getElementById("searchInput").value = ""; addToCart(json.data.data[0].id); return; 
        }

        const container = document.getElementById("productList"); container.innerHTML = "";
        json.data.data.forEach(prod => {
            const col = document.createElement("div"); col.className = "col-lg-3 col-md-4 col-6";
            col.innerHTML = `<div class="card product-card shadow-sm" onclick="addToCart(${prod.id})">
                <span class="badge ${prod.stock <= 5 ? 'bg-danger':'bg-success'} stock-badge">Stock: ${prod.stock}</span>
                <button class="btn btn-dark label-btn shadow-sm" onclick="openLabelModal(event, '${prod.name}', '${prod.current_price}', '${prod.sku}')"><i class="bi bi-tag-fill"></i></button>
                <img src="${prod.image || 'https://via.placeholder.com/150'}" class="product-img card-img-top p-2">
                <div class="card-body p-2 text-center"><h6 class="text-truncate mb-1 fw-bold small">${prod.name}</h6><span class="text-primary fw-bold">$${prod.current_price}</span></div>
            </div>`;
            container.appendChild(col);
        });
        renderPagination(json.data);
    }
    function renderPagination(data) {
        const nav = document.getElementById("paginationNav"); if (data.last_page <= 1) { nav.innerHTML = ""; return; }
        let h = '<ul class="pagination pagination-sm">'; for(let i=1; i<=data.last_page; i++) h += `<li class="page-item ${i==data.current_page?'active':''}"><button class="page-link" onclick="loadProducts(currentCategory, ${i})">${i}</button></li>`;
        nav.innerHTML = h + "</ul>";
    }
    document.getElementById("searchInput").oninput = () => loadProducts(currentCategory);

    // --- Cart ---
    async function loadCart() { const res = await fetch("/api/cart", { headers: {"Authorization": `Bearer ${apiToken}`} }); const data = await res.json(); cartGlobal = data.cart; renderCart(data.cart); }
    async function addToCart(id) { await fetch("/api/cart/add", { method: "POST", headers: {"Authorization": `Bearer ${apiToken}`, "Content-Type": "application/json"}, body: JSON.stringify({product_id: id, quantity: 1}) }); loadCart(); }
    async function updateQty(id, qty) { if (qty < 1) return removeItem(id); await fetch(`/api/cart/item/update/${id}`, { method: "POST", headers: {"Authorization": `Bearer ${apiToken}`, "Content-Type": "application/json"}, body: JSON.stringify({quantity: qty}) }); loadCart(); }
    async function removeItem(id) { await fetch(`/api/cart/item/remove/${id}`, { method: "DELETE", headers: {"Authorization": `Bearer ${apiToken}`} }); loadCart(); }
    async function clearCart() { if(confirm("Clear order?")) { await fetch("/api/cart/clear", { method: "DELETE", headers: {"Authorization": `Bearer ${apiToken}`} }); loadCart(); } }
    
    function renderCart(cart) {
        const container = document.getElementById("cartItems"); const totalEl = document.getElementById("total"); const payBtn = document.getElementById("payBtn");
        if (!cart || !cart.items || cart.items.length === 0) { container.innerHTML = `<div class="text-center py-5 text-muted small">Empty</div>`; totalEl.innerText = "0.00"; payBtn.disabled = true; return; }
        payBtn.disabled = false; let sub = 0; container.innerHTML = "";
        cart.items.forEach(item => {
            const line = (item.price * item.quantity).toFixed(2); sub += parseFloat(line);
            const div = document.createElement("div"); div.className = "card border-0 mb-2 shadow-sm rounded-3 p-2";
            div.innerHTML = `<div class="d-flex justify-content-between align-items-center mb-1"><span class="small fw-bold text-truncate" style="max-width: 150px;">${item.product.name}</span><button class="btn btn-link text-danger p-0" onclick="removeItem(${item.id})"><i class="bi bi-trash"></i></button></div>
            <div class="d-flex justify-content-between align-items-center"><div class="input-group input-group-sm" style="width: 80px;"><button class="btn btn-outline-secondary py-0" onclick="updateQty(${item.id}, ${item.quantity-1})">-</button><input type="text" class="form-control text-center py-0 px-1 border-0" value="${item.quantity}" readonly><button class="btn btn-outline-secondary py-0" onclick="updateQty(${item.id}, ${item.quantity+1})">+</button></div><span class="fw-bold text-primary">$${line}</span></div>`;
            container.appendChild(div);
        });
        totalEl.innerText = sub.toFixed(2); document.getElementById("subtotal").innerText = sub.toFixed(2);
    }

    // --- Barcode Labels (Proper Hardware Test) ---
    function openLabelModal(e, name, price, sku) {
        e.stopPropagation();
        document.getElementById("lpName").innerText = name;
        document.getElementById("lpPrice").innerText = "$" + price;
        document.getElementById("lpSku").innerText = sku;
        
        // Generate scannable lines
        JsBarcode("#barcode", sku, {
            format: "CODE128",
            width: 1.5,
            height: 40,
            displayValue: true,
            fontSize: 12
        });

        if (!labelModal) labelModal = new bootstrap.Modal(document.getElementById("labelModal"));
        labelModal.show();
    }

    function doPrintLabel() {
        const sku = document.getElementById("lpSku").innerText;
        document.getElementById("lName").innerText = document.getElementById("lpName").innerText;
        document.getElementById("lPrice").innerText = document.getElementById("lpPrice").innerText;
        
        // Prepare label print area with lines
        JsBarcode("#labelBarcode", sku, { format: "CODE128", width: 1.5, height: 35, displayValue: true, fontSize: 10 });
        
        document.body.classList.add("is-printing-label");
        window.print();
        document.body.classList.remove("is-printing-label");
    }

    // --- Others ---
    async function showDailyReport() {
        const res = await fetch("/api/pos-report", { headers: {"Authorization": `Bearer ${apiToken}`} }); const json = await res.json();
        const d = json.data; document.getElementById("reportDate").innerText = d.date;
        document.getElementById("repCount").innerText = d.order_count; document.getElementById("repTotal").innerText = "$"+d.total_sales;
        document.getElementById("repCash").innerText = "$"+d.cash_sales; document.getElementById("repCard").innerText = "$"+d.card_sales;
        if (!reportModal) reportModal = new bootstrap.Modal(document.getElementById("reportModal")); reportModal.show();
    }
    async function showPosOrders() {
        const res = await fetch("/api/pos-orders", { headers: {"Authorization": `Bearer ${apiToken}`} }); const json = await res.json();
        const table = document.getElementById("posOrdersTable"); table.innerHTML = "";
        json.data.forEach(order => {
            const tr = document.createElement("tr");
            tr.innerHTML = `<td class="ps-4 fw-bold">#${order.id}</td><td>${new Date(order.created_at).toLocaleDateString()}</td><td class="fw-bold">$${order.total_amount}</td><td>${order.payment_method.toUpperCase()}</td><td class="text-end pe-4"><button class="btn btn-primary btn-sm rounded-pill px-3" onclick="reprintOrder(${order.id})">Invoice</button></td>`;
            table.appendChild(tr);
        });
        if (!posOrdersModal) posOrdersModal = new bootstrap.Modal(document.getElementById("posOrdersModal")); posOrdersModal.show();
    }
    async function reprintOrder(id) {
        const res = await fetch(`/api/orders/${id}`, { headers: {"Authorization": `Bearer ${apiToken}`} }); const json = await res.json();
        if (json.success) {
            const order = json.data; const area = document.getElementById("receiptPrintArea");
            let h = `<div style="text-align:center"><h3>INVOICE #${order.id}</h3><p>${new Date(order.created_at).toLocaleString()}</p></div><hr>`;
            order.items.forEach(i => h += `<div style="display:flex; justify-content:space-between"><span>${i.product_name} x${i.quantity}</span><span>$${(i.price*i.quantity).toFixed(2)}</span></div>`);
            area.innerHTML = h + `<hr><div style="text-align:right"><h4>Total: $${order.total_amount}</h4></div>`; window.print();
        }
    }
    function showCheckoutModal() { if (!checkoutModal) checkoutModal = new bootstrap.Modal(document.getElementById("checkoutModal")); calculateCheckoutTotal(); checkoutModal.show(); }
    function calculateCheckoutTotal() { const s = parseFloat(document.getElementById("subtotal").innerText); const d = parseFloat(document.getElementById("orderDiscount").value) || 0; document.getElementById("finalTotal").innerText = Math.max(0, s-d).toFixed(2); }
    async function submitOrder() {
        const payload = { delivery_name: document.getElementById("custName").value || "Walk-in", delivery_phone: "0000", delivery_email: "pos@shop.com", delivery_address: "Store", payment_method: document.querySelector('input[name="payMethod"]:checked').value, discount_amount: document.getElementById("orderDiscount").value, source: "pos" };
        const res = await fetch("/api/checkout", { method: "POST", headers: {"Authorization": `Bearer ${apiToken}`, "Content-Type": "application/json"}, body: JSON.stringify(payload) });
        const data = await res.json();
        if (data.success) { checkoutModal.hide(); loadCart(); loadProducts(currentCategory); reprintOrder(data.order_id); }
    }

    checkAuth();
</script>
</body>
</html>
