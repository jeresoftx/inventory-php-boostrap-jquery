/**
 * HTML code that generates the functionality of the gear icon in each record of the request
 *
 * @param {string} data id request
 * @returns
 */
const getControls = (data) =>
{
  return `<div class="dropdown">
            <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                class="bi bi-gear-fill" viewBox="0 0 16 16">
                <path
                  d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872l-.1-.34zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z" />
              </svg>
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item edit" data="` + data + `" href="#">Edit</a></li>
              <li><a class="dropdown-item delete" data="` + data + `" href="#"  data-bs-toggle="modal"  data-bs-target="#deleteModal">Delete</a></li>
            </ul>
          </div>`;
}

/**
 * HTML code that generates the product input in the request form
 *
 * @param {string} itemsOption select options string example. <option value="" selected disabled>Choose...</option>
 * @returns
 */
const getProduct = (itemsOption) => {
  return `<div class="col-12 row mt-3 itemRow">
            <label for="" class="col-sm-5 col-form-label">Requested Items:</label>
             <div class="col-sm-5">
              <select class="form-select item itemSelect" name="items[]" required>
              ` + itemsOption + `
              </select>
              <div class="invalid-feedback">
                Please select a valid item.
              </div>
            </div>
            <div class="col-sm-1">
              <button type="button" class="btn btn-sm add" >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                  class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                  <path
                    d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z" />
                </svg>
              </button>
            </div>
            <div class="col-sm-1">
              <button type="button" class="btn btn-sm remove" >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
                </svg>
              </button>
            </div>
          </div>`;
}

$(document).ready(function ()
{
  // Initial configuration of DataTable for the list of requests.
  let table = new DataTable('#requestsTable', {
    ajax: {
      url: '/api.php?q=requests'
    },
    processing: true,
    columnDefs: [ {
      "targets": 0,
      "orderable":false,
      "data": "",
      "render": function ( data, type, row, meta ) {
        return getControls(data);
      }
    },{
      "targets": 2,
      "orderable":false,
      "data": "",
      "render": function ( data, type, row, meta ) {
        return data.map(item => item.name).join(', ');
      }
    } ],
    columns: [
      { data: 'req_id' },
      { data: 'requested_by' },
      { data: 'items' },
      { data: 'type' }
    ]
  });

  /**
   * Declaration of the click event on the delete option inside the gear.
   */
  table.on('click', '.delete', function (e) {
    let data = e.target.getAttribute('data');
    $('#deleteRequestId').html(data);
  });

  /**
   * Declaration of the confirm event on the delete confirmation modal.
   */
  $('#confirmDelete').on('click', function () {
    $.ajax({
      url: 'api.php?q=request&id=' + $('#deleteRequestId').text(),
      dataType: 'json',
      type: 'DELETE',
      success: function ()
      {
        $('#deleteModal').modal('hide');
        $('.toast-body').html('The request has been deleted!');
        const toastLiveExample = $('#liveToast');
        const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
        toastBootstrap.show()
        table.ajax.reload();
      },
    });
  });

  /**
   * Declaration of the click event on the edit option inside the gear.
   */
  table.on('click', '.edit', function (e) {
    let data = e.target.getAttribute('data');
    initEditRequest(data);
    $('#createRequestModal').modal('show');
  });

  let items;
  const initEditRequest = (id) => {
    $.ajax({
      url: 'api.php?q=request&id=' + id,
      dataType: 'json',
      type: 'GET',
      success: function(response) {
        const data = response.data;
        $('#req_id').val(data.req_id);
        $('#user').val(data.requested_by);
        $('#itemType').val(data.itemTypeId);
        $('#itemType').trigger('change');
        items = data.items;
      },
    });
  }

  /**
   * Declaration of the submit event for the create/update request form
   */
  $("#createRequestModal").on('submit', '#createRequestForm', function(event) {
    event.preventDefault();
    $('#save').attr('disabled', 'disabled');
    const formData = $(this).serialize();
    $.ajax({
      url:"api.php?q=requests",
      method: 'POST',
      data: formData,
      success:function(data){
        $('#createRequestForm')[0].reset();
        $('#createRequestModal').modal('hide');
        $('#save').attr('disabled', false);
          $('#deleteModal').modal('hide');
          $('.toast-body').html('The request has been save!');
          const toastLiveExample = $('#liveToast');
          const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
          toastBootstrap.show()
        table.ajax.reload();
      }
    })
  });

  /**
   * When the modal for adding/editing the request is
   * opened, the focus is set, and only one item for products is allowed.
   */
  $('#createRequestModal').on('shown.bs.modal', function () {
    $('#user').trigger('focus')
    $('#products').html(getProduct(itemsOption))
  });

  /**
   * Event to add one more product in the request form.
   */
  $('#products').on('click', 'button.add', function () {
     $('#products').append(getProduct(itemsOption));
  });

  /**
   * Event to remove one more product in the request form.
   */
  $('#products').on('click', 'button.remove', function () {
    if ($('#products .itemRow').length > 1) {
      $(this).closest(".itemRow").fadeOut(500, function(){ $(this).remove(); });
    }
  });

  /**
   * Asynchronously load the catalog of item types.
   */
  $.ajax({
    url: 'api.php?q=itemTypes',
    dataType: 'json',
    type: 'GET',
    success: function(response) {
      var array = response.data;
      for (i in array) {
        $('#itemType').append('<option value="' + array[i].id + '">' + array[i].name + '</option>');
      }
    },
  });

  /**
   * When a type of item is selected, it asynchronously requests
   * the catalog of products of that type and resets the item
   * dropdowns with those options.
   */
  let itemsOption = '';
  $('#itemType').on('change', function () {
    $.ajax({
      url: 'api.php?q=items&itemType='+ this.value,
      dataType: 'json',
      type: 'GET',
      success: function (response)
      {
        if (items) {
          $('#products').empty();
          for (let i = 0; i < items.length; i++) {
            $('#products').append(getProduct());
          }
        }
        var array = response.data;
        $option = '<option value="" selected disabled>Choose...</option>';
        $('.itemSelect').html( $option);
        itemsOption =  $option;
        for (i in array) {
          let option = '<option value="' + array[i].id + '" data-type="' + array[i].type + '">' + array[i].name + '</option>';
          itemsOption += option;
          $('.itemSelect').append(option);
        }
        if (items) {
          $('.itemSelect').each(function (index, element)
          {
            $(element).val(items[index].id);
          });
          items = undefined;
        }
      },
    });
  })

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  const forms = document.querySelectorAll('.needs-validation')

  // Loop over them and prevent submission
  Array.from(forms).forEach(form =>
  {
    form.addEventListener('submit', event =>
    {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }

      form.classList.add('was-validated')
    }, false)
  });

  // remove vlaidation setting when te modal is close
  $("#createRequestModal").on('hidden.bs.modal', function () {
    $('#createRequestForm').removeClass('was-validated')
  });

  /**
   * Reset the entire request form when it is closed."
   */
  $("#cancel").on('click', function () {
    $('#createRequestForm').removeClass('was-validated');
    $('#createRequestForm')[0].reset();
    $('#createRequestModal').modal('hide');
    $('#save').attr('disabled', false);
  });

  /**
   * Cool message for the curious ones who open the console
   */
  console.log("Welcome to the most exciting website in the universe!");
  console.log("Getting ready for an amazing experience...");
  console.log("3...");
  console.log("2...");
  console.log("1...");
  console.log("Launching the fun! 🚀🎉");
});
