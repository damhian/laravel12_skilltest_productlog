@extends('layouts.app')

@section('content')
  <h2 class="mb-4">Product Entry Form</h2>

  <form id="entry-form" class="row g-3 mb-4">
    <div class="col-md-4">
      <label class="form-label">Product name</label>
      <input type="text" name="product_name" class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Quantity in stock</label>
      <input type="number" name="quantity_in_stock" class="form-control" min="0" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Price per item</label>
      <input type="number" step="0.01" name="price_per_item" class="form-control" min="0" required>
    </div>
    <div class="col-12">
      <button class="btn btn-primary">Submit</button>
    </div>
  </form>

  <h2 class="mb-3">Submitted Products</h2>
  <div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="entries-table">
      <thead class="table-light">
        <tr>
          <th>Product name</th>
          <th>Quantity</th>
          <th>Price per item in USD ($)</th>
          <th>Datetime submitted</th>
          <th>Total value</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
        <tr>
          <th colspan="4" class="text-end">Sum total</th>
          <th id="sum-total">0</th>
          <th></th>
        </tr>
      </tfoot>
    </table>
  </div>
@endsection

@section('scripts')
<script>
// 1) Load entries and render table
function fetchEntries() {
  fetch('/entries')
    .then(r => r.json())
    .then(({ entries, sum_total_value }) => {
      const tbody = document.querySelector('#entries-table tbody');
      tbody.innerHTML = '';

      entries.forEach(e => {
        tbody.insertAdjacentHTML('beforeend', `
          <tr data-id="${e.id}">
            <td>${e.product_name}</td>
            <td>${e.quantity_in_stock}</td>
            <td>${Number(e.price_per_item).toFixed(2)}</td>
            <td>${e.datetime_submitted}</td>
            <td>${Number(e.total_value).toFixed(2)}</td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-secondary" onclick="editRow('${e.id}')">Edit</button>
            </td>
          </tr>
        `);
      });

      document.getElementById('sum-total').textContent = Number(sum_total_value).toFixed(2);
    });
}

// 2) Submit form via AJAX
document.getElementById('entry-form').addEventListener('submit', function (ev) {
  ev.preventDefault();
  const formData = new FormData(ev.target);

  fetch('/entries', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': window.csrf },
    body: formData
  })
  .then(row => row.json())
  .then(() => {
    ev.target.reset();
    fetchEntries();
  });
});

// 3) Inline edit row
function editRow(id) {
  const tr = document.querySelector(`tr[data-id="${id}"]`);
  const cells = tr.querySelectorAll('td');

  const [nameCell, qtyCell, priceCell, , , actionsCell] = cells;

  nameCell.innerHTML = `<input class="form-control form-control-sm" value="${nameCell.textContent}">`;
  qtyCell.innerHTML = `<input type="number" class="form-control form-control-sm" value="${qtyCell.textContent}">`;
  priceCell.innerHTML = `<input type="number" step="0.01" class="form-control form-control-sm" value="${priceCell.textContent}">`;

  actionsCell.innerHTML = `
    <div class="d-flex justify-content-end gap-2">
      <button class="btn btn-sm btn-primary" onclick="saveRow('${id}')">Save</button>
      <button class="btn btn-sm btn-outline-secondary" onclick="fetchEntries()">Cancel</button>
    </div>`;
}

function saveRow(id) {
  const tr = document.querySelector(`tr[data-id="${id}"]`);
  const inputs = tr.querySelectorAll('input');

  const payload = new URLSearchParams();
  payload.append('product_name', inputs[0].value);
  payload.append('quantity_in_stock', inputs[1].value);
  payload.append('price_per_item', inputs[2].value);

  fetch(`/entries/${id}`, {
    method: 'PUT',
    headers: {
      'X-CSRF-TOKEN': window.csrf,
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: payload
  })
  .then(row => row.json())
  .then(() => fetchEntries());
}

// 4) Initial load
fetchEntries();
</script>
@endsection