jQuery(document).ready(function($) {
    var rowCount = 1; // Counter for new rows

    // Add new row when clicking 'Add New Row'
    $('#add-row').click(function() {
        var newRow = `
            <tr class="new-row">
                <td><input type="text" name="new_row[` + rowCount + `][word_count_range]" placeholder="Enter Word Count Range" /></td>
                <td><input type="text" name="new_row[` + rowCount + `][price]" placeholder="Enter Price" /></td>
                <td><button type="button" class="button-secondary cancel-row">` + blogtecL10n.cancel + `</button></td>
            </tr>
        `;
        $('#pricing-table tbody').append(newRow);
        rowCount++;
        //$('#add-row').hide(); // Hide Add Row button after adding a new row
    });

    // Cancel the new row addition
    $(document).on('click', '.cancel-row', function() {
        $(this).closest('tr').remove(); // Remove the new row
        $('#add-row').show(); // Show the Add Row button again
    });
});
