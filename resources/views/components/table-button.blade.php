<div class="dropdown font-sans-serif position-static">
    <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">
        <span class="fas fa-ellipsis-h fs-10"></span>
    </button>
    <div class="dropdown-menu dropdown-menu-end border py-0">
        <div class="py-2">
            <a class="dropdown-item" href="{{routeShow($id)}}">
                <i class="fa-solid fa-expand text-primary"></i>
                Visualizar registro
            </a>

            <a class="dropdown-item py-2" href="{{routeEdit($id)}}">
                <i class="fa-solid fa-pen-to-square text-success"></i>
                Editar registro
            </a>
            <form id="delete-form-{{ $id }}" action="{{ route(currentRoute()[0]. '.destroy', $id) }}" method="POST"
                  style="display: none;">
                @csrf
                @method('DELETE')
            </form>

            <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); if(confirm('Tem certeza que deseja remover esse registro?')) { document.getElementById('delete-form-{{ $id }}').submit(); }">
                <i class="fa-solid fa-trash-can text-danger"></i>
                Excluir registro
            </a>
        </div>
    </div>
</div>
