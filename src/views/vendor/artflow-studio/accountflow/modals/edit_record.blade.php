<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Transaction</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm">
                @csrf

                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <input type="text" class="form-control" name="amount" id="edit-amount" required>
                    </div>
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select class="form-control" name="payment_method" id="edit-payment-method" data-control="select2" required>
                            <!-- Populate dynamically -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="account_id">Account ID</label>
                        <select class="form-control" name="account_id" id="edit-account-id" data-control="select2" required>
                            <!-- Populate dynamically -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category ID</label>
                        <select class="form-control" name="category_id" id="edit-category-id" data-control="select2" required>
                            <!-- Populate dynamically -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" class="form-control" name="date" id="edit-date">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" name="description" id="edit-description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>


@push('scripts')
    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Populate edit modal with existing data
            $('#editModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var id = button.data('id');

                // Fetch transaction data from the server
                $.ajax({
                    url: '/accounts/get-transaction/' + id, // Adjust the URL as necessary
                    method: 'POST',
                    success: function(data) {
                        // Populate the fields with the fetched data
                        $('#edit-id').val(data.id);
                        $('#edit-amount').val(data.amount);
                        $('#edit-date').val(data.date);
                        $('#edit-description').val(data.description);

                        // Populate payment methods, accounts, and categories
                        $('#edit-payment-method').empty().append(data.paymentMethods.map(
                            function(method) {
                                return `<option value="${method.id}" ${data.payment_method_id === method.id ? 'selected' : ''}>${method.name}</option>`;
                            }));

                        $('#edit-account-id').empty().append(data.accounts.map(function(
                        account) {
                            return `<option value="${account.id}" ${data.account_id === account.id ? 'selected' : ''}>${account.name}</option>`;
                        }));

                        $('#edit-category-id').empty().append(data.categories.map(function(
                            category) {
                            return `<option value="${category.id}" ${data.category_id === category.id ? 'selected' : ''}>${category.name}</option>`;
                        }));
                    },
                    error: function() {
                        alert('Error fetching data');
                    }
                });
            });
              // Handle the update form submission
    $('#editForm').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        var id = $('#edit-id').val(); // Get the transaction ID

        // Prepare the data to be sent in the AJAX request
        var formData = $(this).serialize();

        // Send the update request
        $.ajax({
            url: '/accounts/update-transaction/' + id, // Adjust the URL as necessary
            method: 'post',
            data: formData,
            success: function(response) {
                // Close the modal
                $('#editModal').modal('hide');

                // Optionally, refresh the table or display a success message
                alert(response.message); // Or use a toast notification
                location.reload(); // Reload the page or update the table as needed
            },
            error: function(xhr) {
                // Handle errors
                var errors = xhr.responseJSON.errors;
                if (errors) {
                    // Display validation errors
                    for (var key in errors) {
                        alert(errors[key][0]); // Display the first error message for each field
                    }
                } else {
                    alert('Error updating transaction');
                }
            }
        });
    });
        });
    </script>
@endpush
