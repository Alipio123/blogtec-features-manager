jQuery(document).ready(function($) {
    var rowCount = 1; // Counter for new rows
    $('#add-row').click(function() {
        var newRow = `
            <tr>
                <td><input type="text" name="new_row[` + rowCount + `][word_count_range]" placeholder="Enter Word Count Range" /></td>
                <td><input type="text" name="new_row[` + rowCount + `][price]" placeholder="Enter Price" /></td>
            </tr>
        `;
        $('#pricing-table tbody').append(newRow);
        rowCount++;
    });
});
