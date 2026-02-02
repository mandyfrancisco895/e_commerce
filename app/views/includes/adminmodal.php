<!-- ADMIN PRODUCT ACTION -->

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="addProductForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (empty($activeCategories)): ?>
                        <!-- No categories available message -->
                        <div class="alert alert-warning text-center">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>No categories available!</strong><br>
                            Please add at least one active category before creating a product.
                        </div>
                        
                    <?php else: ?>
                        <!-- Product form fields -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Product Name</label>
                                <input type="text" name="name" class="form-control" required placeholder="Enter product name">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">-- Select Category --</option>
                                    <?php foreach ($activeCategories as $cat): ?>
                                        <option value="<?= $cat['id']; ?>"><?= htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Price</label>
                                <input type="number" name="price" class="form-control" placeholder="0.00" step="0.01" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" name="stock" class="form-control" placeholder="0" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="">-- Select Status --</option>
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="pending">Pending</option>
                                </select>  
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold d-block">Available Sizes</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="sizeXS" name="sizes[]" value="XS">
                                        <label class="form-check-label" for="sizeXS">XS</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="sizeS" name="sizes[]" value="S">
                                        <label class="form-check-label" for="sizeS">S</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="sizeM" name="sizes[]" value="M">
                                        <label class="form-check-label" for="sizeM">M</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="sizeL" name="sizes[]" value="L">
                                        <label class="form-check-label" for="sizeL">L</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="sizeXL" name="sizes[]" value="XL">
                                        <label class="form-check-label" for="sizeXL">XL</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="sizeXXL" name="sizes[]" value="XXL">
                                        <label class="form-check-label" for="sizeXXL">XXL</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3" placeholder="Product description"></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Product Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <?php if (!empty($activeCategories)): ?>
                        <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Product Modal -->
<div class="modal fade" id="viewProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Product Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <div class="text-center mb-4">
            <img id="view_image" src="" alt="Product Image" class="img-fluid rounded" style="max-height: 500px; width: 100%; object-fit: contain;">

            </div>
          </div>
          <div class="col-md-6">
            <table class="table table-borderless">
              <tr>
                <th width="120">Product Name:</th>
                <td id="view_name"></td>
              </tr>
              <tr>
                <th>ID:</th>
                <td id="view_id"></td>
              </tr>
              <tr>
                <th>Category:</th>
                <td id="view_category"></td>
              </tr>
              <tr>
                <th>Price:</th>
                <td id="view_price"></td>
              </tr>
              <tr>
                <th>Stock:</th>
                <td id="view_stock"></td>
              </tr>
              <tr>
                <th>Status:</th>
                <td id="view_status"></td>
              </tr>
              <tr>
                <th>Sizes:</th>
                <td id="view_sizes"></td>
              </tr>
              <tr>
                <th>Description:</th>
                <td id="view_description"></td>
              </tr>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" name="edit_id" id="edit_id">

          <div class="mb-3">
            <label for="edit_name" class="form-label">Product Name</label>
            <input type="text" class="form-control" id="edit_name" name="edit_name" required>
          </div>

          <div class="mb-3">
            <label for="edit_description" class="form-label">Description</label>
            <textarea class="form-control" id="edit_description" name="edit_description" required></textarea>
          </div>

          <div class="mb-3">
            <label for="edit_price" class="form-label">Price</label>
            <input type="number" step="0.01" class="form-control" id="edit_price" name="edit_price" required>
          </div>

          <div class="mb-3">
            <label for="edit_stock" class="form-label">Stock</label>
            <input type="number" class="form-control" id="edit_stock" name="edit_stock" required>
          </div>

          <div class="mb-3">
            <label for="edit_category" class="form-label">Category</label>
            <select class="form-select" id="edit_category" name="edit_category" required>
              <option value="">-- Select Category --</option>
              <?php foreach ($activeCategories as $cat): ?>
                <option value="<?= $cat['id']; ?>"><?= htmlspecialchars($cat['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Sizes -->
        <div class="mb-3">
        <label class="form-label">Sizes</label><br>
        <?php 
            $allSizes = ["XS", "S", "M", "L", "XL", "XXL"];
        ?>
        <?php foreach ($allSizes as $size): ?>
            <div class="form-check form-check-inline">
            <input 
                class="form-check-input" 
                type="checkbox" 
                name="edit_sizes[]" 
                id="edit_size_<?= $size ?>" 
                value="<?= $size ?>"
            >
            <label class="form-check-label" for="edit_size_<?= $size ?>"><?= $size ?></label>
            </div>
        <?php endforeach; ?>
        <!-- Store old sizes for JS prefill -->
        <input type="hidden" id="edit_sizes_hidden" name="edit_sizes_hidden">
        </div>


          <div class="col-md-12">
            <label class="form-label">Status</label>
            <!-- In Edit Product Modal -->
        <select class="form-select" id="edit_status" name="edit_status" required>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="pending">Pending</option>
        </select>
            
            </div>

           

          <div class="mb-3">
            <label class="form-label">Current Image</label><br>
            <img id="current_image" src="" alt="Product Image" style="max-width:150px; display:none;">
            <input type="hidden" name="current_image_hidden" id="current_image_hidden">
          </div>

          <div class="mb-3">
            <label for="edit_image" class="form-label">Upload New Image</label>
            <input type="file" class="form-control" id="edit_image" name="edit_image">
          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" name="update_product" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- ADMIN CATEGORY ACTION -->

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="category_name" class="form-control" required  placeholder="Enter category name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="category_description" rows="3" placeholder="Category description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="category_status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_category_id" id="edit_category_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="edit_category_name" id="edit_category_name" class="form-control" required placeholder="Enter category name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="edit_category_description"  id="edit_category_description" rows="3" placeholder="Category description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="edit_category_status" id="edit_category_status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_category" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- ADMIN CUSTOMER ACTION -->


<!-- View Customer Modal -->
<div class="modal fade" id="viewCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Customer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-4">
                        <div class="avatar mx-auto mb-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px; font-size: 2rem;" id="view_customer_avatar">
                            --
                        </div>
                        <h5 id="view_customer_name">Customer Name</h5>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <th width="140">Customer ID:</th>
                                <td id="view_customer_id">-</td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td id="view_customer_email">-</td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td id="view_customer_phone">-</td>
                            </tr>
                            <tr>
                                <th>Total Orders:</th>
                                <td id="view_customer_orders">-</td>
                            </tr>
                            <tr>
                                <th>Total Spent:</th>
                                <td id="view_customer_spent" class="fw-bold">-</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td><span id="view_customer_status" class="badge bg-secondary">-</span></td>
                            </tr>
                            <tr>
                                <th>Date Joined:</th>
                                <td id="view_customer_joined">-</td>
                            </tr>
                            <tr>
                                <th>Address:</th>
                                <td id="view_customer_address">-</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Recent Orders Section -->
                <div class="mt-4">
                    <h6 class="fw-bold mb-3">Recent Orders</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="customer_orders_table">
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">
                                        Click to load orders...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCustomerModalLabel">
                    <i class="bi bi-person-gear me-2"></i>Edit Customer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form method="POST" id="editCustomerForm" action="admin-dashboard.php">
                <div class="modal-body">
                    <!-- Hidden customer ID field -->
                    <input type="hidden" id="edit_customer_id" name="edit_customer_id" value="">
                    
                    <div class="row g-3">
                        <!-- First Name -->
                        <div class="col-md-6">
                            <label for="edit_customer_first_name" class="form-label">
                                First Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_customer_first_name" 
                                   name="edit_customer_first_name" 
                                   required>
                        </div>
                        
                        <!-- Last Name -->
                        <div class="col-md-6">
                            <label for="edit_customer_last_name" class="form-label">
                                Last Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_customer_last_name" 
                                   name="edit_customer_last_name" 
                                   required>
                        </div>
                        
                        <!-- Email -->
                        <div class="col-12">
                            <label for="edit_customer_email" class="form-label">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control" 
                                   id="edit_customer_email" 
                                   name="edit_customer_email" 
                                   required>
                        </div>
                        
                        <!-- Phone -->
                        <div class="col-md-6">
                            <label for="edit_customer_phone" class="form-label">Phone Number</label>
                            <input type="tel" 
                                   class="form-control" 
                                   id="edit_customer_phone" 
                                   name="edit_customer_phone" 
                                   placeholder="Optional">
                        </div>
                        
                        <!-- Status (Read-Only Display) -->
                        <div class="col-md-6">
                            <label for="edit_customer_status_display" class="form-label">Status</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_customer_status_display" 
                                   readonly 
                                   style="background-color: #f8f9fa;">
                            <div class="form-text" id="statusDescription">
                                Current customer status (read-only).
                            </div>
                        </div>
                        
                        <!-- Address -->
                        <div class="col-12">
                            <label for="edit_customer_address" class="form-label">Address</label>
                            <textarea class="form-control" 
                                      id="edit_customer_address" 
                                      name="edit_customer_address" 
                                      rows="3" 
                                      placeholder="Customer's address (optional)"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="submit" name="update_customer" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Update Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Profile Admin View Modal - FIXED VERSION -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="profileModalLabel">
                    <i class="bi bi-person-circle me-2"></i>My Profile
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Avatar + Basic Info -->
                    <div class="col-md-4 text-center">
                        <div class="profile-image-container large">
                            <?php
                            // FIXED: Use same logic as dropdown for consistency
                            $modalAvatarPath = '';
                            $modalWebPath = '';
                            
                            // Priority: Session first (most up-to-date), then currentUser
                            if (!empty($_SESSION['profile_pic'])) {
                                $modalWebPath = $_SESSION['profile_pic'];
                                $modalAvatarPath = $_SERVER['DOCUMENT_ROOT'] . $_SESSION['profile_pic'];
                            } elseif (!empty($_SESSION['avatar'])) {
                                $modalWebPath = $_SESSION['avatar'];
                                $modalAvatarPath = $_SERVER['DOCUMENT_ROOT'] . $_SESSION['avatar'];
                            } elseif (!empty($currentUser['profile_pic'])) {
                                $modalWebPath = '/E-COMMERCE/public/uploads/' . $currentUser['profile_pic'];
                                $modalAvatarPath = $_SERVER['DOCUMENT_ROOT'] . '/E-COMMERCE/public/uploads/' . $currentUser['profile_pic'];
                            }
                            
                            $modalAvatarInitial = !empty($_SESSION['username']) ? 
                                strtoupper(substr($_SESSION['username'], 0, 1)) : 
                                (!empty($currentUser['username']) ? strtoupper(substr($currentUser['username'], 0, 1)) : 'U');
                            
                            $modalImageExists = !empty($modalAvatarPath) && file_exists($modalAvatarPath);
                            
                            // Debug for modal
                            if (isset($_GET['debug'])) {
                                echo "<!-- MODAL DEBUG: Web Path: " . htmlspecialchars($modalWebPath) . " -->";
                                echo "<!-- MODAL DEBUG: Server Path: " . htmlspecialchars($modalAvatarPath) . " -->";
                                echo "<!-- MODAL DEBUG: File Exists: " . ($modalImageExists ? 'Yes' : 'No') . " -->";
                            }
                            ?>
                            
                            <?php if ($modalImageExists): ?>
                                <img src="<?php echo htmlspecialchars($modalWebPath); ?>?v=<?php echo time(); ?>" 
                                     class="profile-image" 
                                     alt="Profile Picture"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="profile-image-placeholder" style="display: none;">
                                    <?php echo $modalAvatarInitial; ?>
                                </div>
                            <?php else: ?>
                                <div class="profile-image-placeholder">
                                    <?php echo $modalAvatarInitial; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h5><?php echo htmlspecialchars($_SESSION['username'] ?? $currentUser['username'] ?? 'User'); ?></h5>
                        <p class="text-muted"><?php echo ucfirst($_SESSION['role'] ?? $currentUser['role'] ?? 'User'); ?></p>
                    </div>

                    <!-- Profile Details -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <!-- Username -->
                                <div class="row mb-3">
                                    <label class="col-sm-4 col-form-label fw-bold">Username:</label>
                                    <div class="col-sm-8">
                                        <p class="form-control-plaintext">
                                            <?php echo htmlspecialchars($_SESSION['username'] ?? $currentUser['username'] ?? ''); ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="row mb-3">
                                    <label class="col-sm-4 col-form-label fw-bold">Email:</label>
                                    <div class="col-sm-8">
                                        <p class="form-control-plaintext">
                                            <?php echo htmlspecialchars($_SESSION['email'] ?? $currentUser['email'] ?? ''); ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Role -->
                                <div class="row mb-3">
                                    <label class="col-sm-4 col-form-label fw-bold">Role:</label>
                                    <div class="col-sm-8">
                                        <span class="badge bg-primary">
                                            <?php echo ucfirst($_SESSION['role'] ?? $currentUser['role'] ?? 'User'); ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Phone -->
                                <div class="row mb-3">
                                    <label class="col-sm-4 col-form-label fw-bold">Phone:</label>
                                    <div class="col-sm-8">
                                        <p class="form-control-plaintext">
                                            <i class="bi bi-telephone-fill text-primary me-1"></i>
                                            <?php 
                                                $phone = $_SESSION['phone'] ?? $currentUser['phone'] ?? '';
                                                echo !empty($phone) ? 
                                                    htmlspecialchars($phone) : 
                                                    '<span class="text-muted fst-italic">Not provided</span>'; 
                                            ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="row mb-3">
                                    <label class="col-sm-4 col-form-label fw-bold">Address:</label>
                                    <div class="col-sm-8">
                                        <p class="form-control-plaintext">
                                            <i class="bi bi-geo-alt-fill text-primary me-1"></i>
                                            <?php 
                                                $address = $_SESSION['address'] ?? $currentUser['address'] ?? '';
                                                echo !empty($address) ? 
                                                    htmlspecialchars($address) : 
                                                    '<span class="text-muted fst-italic">Not provided</span>'; 
                                            ?>
                                        </p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="openEditProfile()">
                    <i class="bi bi-pencil me-2"></i>Edit Profile
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal - FIXED VERSION -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="editProfileModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Profile
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" id="editProfileForm" enctype="multipart/form-data">
    <div class="modal-body">
        <div class="row">
             <!-- Avatar Section with Upload -->
             <div class="col-md-4 text-center">
                <div class="profile-image-container mb-3">
                <?php
                // FIXED: Use same logic as dropdown and profile modal
                $editAvatarPath = '';
                $editWebPath = '';
                
                // Priority: Session first (most up-to-date), then currentUser
                if (!empty($_SESSION['profile_pic'])) {
                    $editWebPath = $_SESSION['profile_pic'];
                    $editAvatarPath = $_SERVER['DOCUMENT_ROOT'] . $_SESSION['profile_pic'];
                } elseif (!empty($_SESSION['avatar'])) {
                    $editWebPath = $_SESSION['avatar'];
                    $editAvatarPath = $_SERVER['DOCUMENT_ROOT'] . $_SESSION['avatar'];
                } elseif (!empty($currentUser['profile_pic'])) {
                    $editWebPath = '/E-COMMERCE/public/uploads/' . $currentUser['profile_pic'];
                    $editAvatarPath = $_SERVER['DOCUMENT_ROOT'] . '/E-COMMERCE/public/uploads/' . $currentUser['profile_pic'];
                }
                
                $editAvatarInitial = !empty($_SESSION['username']) ? 
                    strtoupper(substr($_SESSION['username'], 0, 1)) : 
                    (!empty($currentUser['username']) ? strtoupper(substr($currentUser['username'], 0, 1)) : 'U');
                
                $editImageExists = !empty($editAvatarPath) && file_exists($editAvatarPath);
                
                // Debug for edit modal
                if (isset($_GET['debug'])) {
                    echo "<!-- EDIT DEBUG: Web Path: " . htmlspecialchars($editWebPath) . " -->";
                    echo "<!-- EDIT DEBUG: Server Path: " . htmlspecialchars($editAvatarPath) . " -->";
                    echo "<!-- EDIT DEBUG: File Exists: " . ($editImageExists ? 'Yes' : 'No') . " -->";
                }
                ?>
                
                <?php if ($editImageExists): ?>
                    <img id="profileImagePreview" src="<?php echo htmlspecialchars($editWebPath); ?>?v=<?php echo time(); ?>" 
                        class="profile-image" 
                        alt="Profile Picture"
                        onerror="this.style.display='none'; document.getElementById('profileImagePlaceholder').style.display='flex';">
                    <div id="profileImagePlaceholder" class="profile-image-placeholder" style="display: none;">
                        <?php echo $editAvatarInitial; ?>
                    </div>
                <?php else: ?>
                    <div id="profileImagePlaceholder" class="profile-image-placeholder">
                        <?php echo $editAvatarInitial; ?>
                    </div>
                    <img id="profileImagePreview" src="" class="profile-image" 
                         style="display: none;" 
                         alt="Profile Preview">
                <?php endif; ?>
                </div>
                
                <div class="upload-btn-wrapper">
                    <label for="profileImage" class="btn btn-sm btn-outline-primary mb-1">
                        <i class="bi bi-upload me-1"></i>Upload Image
                    </label>
                    <input type="file" id="profileImage" name="profile_pic" 
                           accept="image/*" onchange="previewImage(this)" style="display: none;">
                </div>
                <div class="form-text">Max 2MB • JPG, PNG</div>
                
                <?php if ($editImageExists): ?>
                <div class="remove-image-option mt-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remove_profile_pic" id="removeProfilePic">
                        <label class="form-check-label text-danger small" for="removeProfilePic">
                            Remove current image
                        </label>
                    </div>
                </div>
                <?php endif; ?>
            </div>  

            <!-- Editable Fields -->
            <div class="col-md-8">
                <!-- Username -->
                <div class="mb-3">
                    <label for="editUsername" class="form-label fw-bold">Username</label>
                    <input type="text" class="form-control" id="editUsername" name="username" 
                           value="<?php echo htmlspecialchars($_SESSION['username'] ?? $currentUser['username'] ?? ''); ?>" required>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="editEmail" class="form-label fw-bold">Email</label>
                    <input type="email" class="form-control" id="editEmail" name="email" 
                           value="<?php echo htmlspecialchars($_SESSION['email'] ?? $currentUser['email'] ?? ''); ?>" required>
                </div>

                <!-- Phone -->
                <div class="mb-3">
                    <label for="editPhone" class="form-label fw-bold">Phone (Optional)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                        <input type="tel" class="form-control" id="editPhone" name="phone" 
                               placeholder="Enter your phone number"
                               value="<?php echo htmlspecialchars($_SESSION['phone'] ?? $currentUser['phone'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Address -->
                <div class="mb-3">
                    <label for="editAddress" class="form-label fw-bold">Address (Optional)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                        <textarea class="form-control" id="editAddress" name="address" rows="2" 
                                  placeholder="Enter your full address"><?php echo htmlspecialchars($_SESSION['address'] ?? $currentUser['address'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="hidden" name="action" value="update_profile">
        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id'] ?? ''; ?>">
        <input type="hidden" name="existing_profile_pic" value="<?php 
            // Store the filename for backend processing
            if (!empty($_SESSION['profile_pic'])) {
                echo htmlspecialchars(basename($_SESSION['profile_pic']));
            } elseif (!empty($currentUser['profile_pic'])) {
                echo htmlspecialchars($currentUser['profile_pic']);
            }
        ?>">
        
        <button type="submit" class="btn btn-success">
            <i class="bi bi-check-lg me-2"></i>Save Changes
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    </div>
</form>
        </div>
    </div>
</div>


<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="changePasswordModalLabel">
                    <i class="bi bi-shield-lock me-2"></i>Change Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="changePasswordForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label fw-bold">Current Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('currentPassword')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label fw-bold">New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="newPassword" name="new_password" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('newPassword')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Password must be at least 6 characters long.</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label fw-bold">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmPassword')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-shield-check me-2"></i>Change Password
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>




<!-- View Order Modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-receipt me-2"></i>Order Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <div class="card border-0 bg-light">
              <div class="card-body">
                <h6 class="card-title mb-3">
                  <i class="bi bi-person me-2"></i>Customer Information
                </h6>
                <table class="table table-borderless table-sm">
                  <tr>
                    <th width="100">Customer:</th>
                    <td id="view_customer"></td>
                  </tr>
                  <tr>
                    <th>Email:</th>
                    <td id="view_email"></td>
                  </tr>
                  <tr>
                    <th>Phone:</th>
                    <td id="view_phone"></td>
                  </tr>
                  <tr>
                    <th>Address:</th>
                    <td id="view_address"></td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card border-0 bg-primary bg-opacity-10">
              <div class="card-body">
                <h6 class="card-title mb-3 text-primary">
                  <i class="bi bi-info-circle me-2"></i>Order Information
                </h6>
                <table class="table table-borderless table-sm">
                  <tr>
                    <th width="100">Order ID:</th>
                    <td id="view_order_id"></td>
                  </tr>
                  <tr>
                    <th>Order Number:</th>
                    <td id="view_order_number"></td>
                  </tr>
                  <tr>
                    <th>Total Amount:</th>
                    <td id="view_total_amount" class="fw-bold text-success"></td>
                  </tr>
                  <tr>
                    <th>Status:</th>
                    <td id="view_order_status"></td>
                  </tr>
                  <tr>
                    <th>Items Count:</th>
                    <td id="view_items_count"></td>
                  </tr>
                  <tr>
                    <th>Order Date:</th>
                    <td id="view_order_date"></td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Order Items Section -->
        <div class="mt-4">
          <h6 class="fw-semibold mb-3">
            <i class="bi bi-bag me-2"></i>Order Items
          </h6>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead class="table-light">
                <tr>
                  <th>Product</th>
                  <th>Size</th>
                  <th>Quantity</th>
                  <th>Price</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody id="view_order_items">
                <!-- Order items will be populated here -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i>Close
        </button>
        <button type="button" class="btn btn-primary" id="printOrderBtn">
          <i class="bi bi-printer me-1"></i>Print Invoice
        </button>
      </div>
    </div>
  </div>
</div>

