document.addEventListener('DOMContentLoaded', function() {
    const editCategoryModal = document.getElementById('editCategoryModal');
    
    editCategoryModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        
        // Get data from the edit button
        const categoryId = button.getAttribute('data-id');
        const categoryName = button.getAttribute('data-name');
        const categoryDescription = button.getAttribute('data-description');
        const categoryStatus = button.getAttribute('data-status');
        
        // Populate the form fields
        document.getElementById('edit_category_id').value = categoryId;
        document.getElementById('edit_category_name').value = categoryName;
        document.getElementById('edit_category_description').value = categoryDescription;
        document.getElementById('edit_category_status').value = categoryStatus;
    });
});