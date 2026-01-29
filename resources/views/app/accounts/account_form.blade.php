<div class="row">
    <x-input col="12" set="" type="text" title="Banco" id="bank_name" name="bank_name" value="{{ old('bank_name', $account->bank_name ?? '') }}" placeholder="Bradesco, Nubank .." disabled=""></x-input>
</div>

<div class="row">
    <x-input-price col="6" title="Saldo atual" id="current_balance" name="current_balance" value="{{ old('current_balance', $account->current_balance ?? '') }}" />
</div>

<div class="row mt-3">
    <x-input-check col="12" set="" id="corrente" value="1" name="type" title="Conta corrente" checked="1" disabled=""></x-input-check>
    <x-input-check col="12" set="" id="poupanca" value="2" name="type" title="Conta poupança" checked="" disabled=""></x-input-check>
    <x-input-check col="12" set="" id="investimento" value="3" name="type" title="Conta de investimento" checked="" disabled=""></x-input-check>
</div>


@push('scripts')
    <script src="{{asset('assets/js/common/mask_price_input.js')}}"></script>
@endpush
