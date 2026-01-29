@if($button == 'novo')
    <a href="{{routeCreate()}}" class="btn btn-falcon-default btn-sm" type="button">
        <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
        <span class="d-none d-sm-inline-block ms-1">Novo</span>
    </a>
@elseif($button == 'atualizar')
    <a href="#" class="btn btn-falcon-default btn-sm" type="button">
        <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
        <span class="d-none d-sm-inline-block ms-1">Atualizar</span>
    </a>
@elseif($button == 'cadastrar')
    <button type="submit" class="btn btn-falcon-default btn-sm">
        <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
        <span class="d-none d-sm-inline-block ms-1">Salvar</span>
    </button>
@endif
