# TODO - Sale Functionality Fix

## Tasks to Complete:

### 1. Sale.php Controller Updates
- [ ] Fix title in `cadastro()` method (change from "Página inicial" to "Cadastro de Venda")
- [ ] Implement `alterar($request, $response, $args)` - Load existing sale for editing
- [ ] Implement `print($request, $response)` - Print sale
- [ ] Implement `update($request, $response)` - Update sale totals and details
- [ ] Implement `insertItem($request, $response)` - Add item to sale
- [ ] Implement `deletar($request, $response, $args)` - Delete a sale
- [ ] Implement `deleteItem($request, $response, $args)` - Delete item from sale
- [ ] Improve `insert()` method to accept additional parameters

### 2. sale.js JavaScript Updates
- [ ] Add `cart` array variable to track items
- [ ] Add `discount` object variable for discounts
- [ ] Add `paymentMethod` variable
- [ ] Implement `addToCart()` function to add products to cart
- [ ] Implement `updateCart()` function to render cart items
- [ ] Implement `updateTotals()` function to calculate totals
- [ ] Fix F9 key handler to add items to cart
- [ ] Fix finalize button to save complete sale
- [ ] Fix cancel button functionality

### 3. sale.html View Updates (if needed)
- [ ] Ensure proper structure for discount inputs with correct classes

## Status: In Progress
