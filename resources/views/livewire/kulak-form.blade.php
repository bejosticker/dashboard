<div>
    @if (session()->has('success'))
        <div style="color: green; margin-bottom: 15px; padding: 10px; border: 1px solid green; background-color: #e6ffe6; border-radius: 5px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="margin-bottom: 15px;display: flex; flex-direction: row; align-items: end;" class="row">
        <div class="col-md-4">
            <label for="supplierSelect" class="form-label">Pilih Supplier:</label>
            <select wire:model.live="supplierId" id="supplierSelect" class="form-control">
                <option value="">-- Pilih Supplier --</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier['id'] }}">{{ $supplier['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal Pembelian:</label>
            <input type="date" wire:model.live="date" name="date" class="form-control" id="">
        </div>
        <div class="col-md-4">
            <h5 class="m-0">Total: {{formatRupiah($total)}}</h5>
        </div>
    </div>

    <hr>

    <div style="display: flex; gap: 10px; margin-bottom: 8px; flex-direction: row; align-items: center; font-weight: bold;">
        <b style="width: 30px; text-align: center;">##</b>
        <p class="w-100 m-0"><b>Produk</b></p>
        <p class="w-100 m-0"><b>Quantity (Roll)</b></p>
        <p class="w-100 m-0"><b>Harga</b></p>
        <p class="w-100 m-0"><b>Subtotal</b></p>
        <div style="width: 40px;"></div>
    </div>

    @foreach ($items as $i => $item)
        <div style="display: flex; gap: 10px; margin-bottom: 8px; flex-direction: row; align-items: center;" wire:key="item-{{ $i }}">
            <!-- <b style="width: 30px; text-align: center;">{{$loop->iteration}}. </b> -->
             <input type="checkbox" wire:model="items.{{ $i }}.include" class="form-check-input" style="width: 20px; height: 20px;">
            
            <select wire:model.live="items.{{ $i }}.product_id" class="form-control" name="item.{{$i}}">
                <option value="">-- Pilih Produk --</option>
                @foreach ($products as $product)
                    <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
                @endforeach
            </select>

            <input type="number" name="jumlah.{{$i}}" class="form-control" wire:model.live="items.{{ $i }}.jumlah" placeholder="Jumlah" />
            <input type="number" name="harga.{{$i}}" class="form-control" wire:model.live="items.{{ $i }}.harga" placeholder="Harga" />
            <input type="number" name="subtotal.{{$i}}" class="form-control" value="{{ $item['subtotal'] }}" readonly />

            <button type="button" class="btn btn-danger" wire:click.prevent="removeItem({{ $i }})">
                <span class="tf-icons bx bx-trash"></span>
            </button>
        </div>
    @endforeach

    <!-- <button type="button" class="btn btn-info" wire:click="addItem" style="margin-top: 15px;">
        <span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Produk
    </button> -->
    <br><br>
    <div class="modal-footer p-0">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" wire:click="save">Simpan</button>
    </div>
</div>
