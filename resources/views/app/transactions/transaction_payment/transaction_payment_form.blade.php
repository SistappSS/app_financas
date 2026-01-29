<form id="paymentForm" method="POST">
    @csrf
    <input type="hidden" name="transaction_id" id="payment_transaction_id">

    <div class="mb-3">
        <label class="form-label">Valor pago</label>
        <input type="number" step="0.01" name="amount" id="payment_amount" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Data do pagamento</label>
        <input type="date" name="payment_date" id="payment_date" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-success w-100">Salvar</button>
</form>
