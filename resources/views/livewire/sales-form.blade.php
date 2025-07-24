<div>
    @if (session()->has('success'))
        <div style="color: green; margin-bottom: 15px; padding: 10px; border: 1px solid green; background-color: #e6ffe6; border-radius: 5px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="margin-bottom: 15px;display: flex; flex-direction: row; align-items: end;" class="row">
        <div class="col-md-4">
            <label class="form-label">Tanggal Penjualan:</label>
            <input type="date" wire:model.live="date" name="date" class="form-control" id="">
        </div>
        <div class="col-md-4">
             <label class="form-label">Nama Customer (opsional):</label>
            <input type="text" wire:model.live="customer" name="customer" class="form-control" id="">
        </div>
    </div>

    <hr>

    @if ($date != '')

    <div style="display: flex; gap: 10px; margin-bottom: 8px; flex-direction: row; align-items: center; font-weight: bold;">
        <b style="width: 30px; text-align: center;">No.</b>
        <p class="w-100 m-0"><b>Produk</b></p>
        <p class="w-100 m-0"><b>Jenis Harga</b></p>
        <p class="w-100 m-0"><b>Quantity</b></p>
        <p class="w-100 m-0"><b>Harga</b></p>
        <p class="w-100 m-0"><b>Subtotal</b></p>
        <div style="width: 40px;"></div>
    </div>

    @foreach ($items as $i => $item)
        <div style="display: flex; gap: 10px; margin-bottom: 8px; flex-direction: row; align-items: center;" wire:key="item-{{ $i }}">
            <b style="width: 30px; text-align: center;">{{$loop->iteration}}. </b>
            
            <select wire:model.live="items.{{ $i }}.product_id" class="form-control" name="item.{{$i}}">
                <option value="">-- Pilih Produk --</option>
                @foreach ($products as $product)
                    <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
                @endforeach
            </select>

            <select wire:model.live="items.{{ $i }}.price_type" class="form-control">
                <option value="">-- Pilih Jenis Harga --</option>
                @foreach ($prices as $price)
                    <option value="{{ $price }}">{{ convertPriceType($price) }}</option>
                @endforeach
            </select>

            <div class="input-group input-group-merge">
                <input type="number" name="jumlah.{{$i}}" class="form-control" wire:model.live="items.{{ $i }}.jumlah" placeholder="Jumlah" />
                <span class="input-group-text">{{ in_array($item['price_type'], ['price_agent', 'price_grosir', 'price_umum_roll']) ? 'Roll' : 'Meter' }}</span>
            </div>
            <input type="number" name="price.{{$i}}" class="form-control" wire:model.live="items.{{ $i }}.price" placeholder="Harga" readonly />
            <input type="number" name="subtotal.{{$i}}" class="form-control" value="{{ $item['subtotal'] }}" readonly />

            <button type="button" class="btn btn-danger" wire:click.prevent="removeItem({{ $i }})">
                <span class="tf-icons bx bx-trash"></span>
            </button>
        </div>
    @endforeach

    <button type="button" class="btn btn-info" wire:click="addItem" style="margin-top: 15px;">
        <span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Produk
    </button>
    <hr>
    <div class="row flex-row align-items-end">
        <div class="col-md-4">
            <label class="form-label">Diskon (Rp):</label>
            <input type="number" name="discount}" class="form-control" wire:model.live="discount" placeholder="Diskon" />
        </div>
        <div class="col-md-4">
            <label class="form-label">Metode Pembayaran:</label>
            <select wire:model.live="payment_method_id" class="form-control" name="payment_method">
                <option value="">-- Pilih Metode Pembayaran --</option>
                @foreach ($paymentMethods as $pm)
                    <option value="{{ $pm['id'] }}">{{ $pm['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <h5 class="m-0" style="text-align:right;">Total: {{formatRupiah($total)}}</h5>
        </div>
    </div>
     <hr>
    <br><br>
    <div class="modal-footer p-0">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" wire:click="save">Simpan</button>
    </div>
    @endif
</div>
