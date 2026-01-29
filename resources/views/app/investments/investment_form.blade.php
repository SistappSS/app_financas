<div class="row">
    <x-input col="12" set="" type="text" title="Nome do investimento" id="name" name="name" placeholder="CDB Banco X" required></x-input>
</div>

<div class="row">
    <x-input col="6" set="" type="text" title="Valor de compra (R$)" id="purchase_value" name="purchase_value" placeholder="1.000,00" required></x-input>

    <x-input col="3" set="" type="text" title="Taxa (%)" id="interest_rate" name="interest_rate" placeholder="1,10" required></x-input>

    <x-select col="3" set="" name="rate_period" id="rate_period" title="Período">
        <option value="monthly" selected>Mensal</option>
        <option value="yearly">Anual</option>
    </x-select>
</div>

<div class="row">
    <x-input col="6" set="" type="date" title="Início (opcional)" id="start_date" name="start_date"></x-input>
    <x-input col="6" set="" type="text" title="Observações" id="notes" name="notes" placeholder="Opcional"></x-input>
</div>

