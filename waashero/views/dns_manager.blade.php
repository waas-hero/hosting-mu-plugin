<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.0.4/tailwind.min.css" integrity="sha512-paVHVRRhdoOu1nOXKnqDC1Vka0nh7FAmU3nsM4n2FKxOQTeF6crMdMfkVvEsuaOXZ6oEAVL5+wLbQcule/Xdag==" crossorigin="anonymous" />

{{-- BEGIN PAGE CONTAINER --}}
<div class="page-container max-w-7xl mx-auto mr-4">

<div class="mt-8">

    <h1 class="text-gray-800 text-2xl mb-4">
        
        <?php esc_html_e( 'Domain Manager' );?>

    </h1>

    @if($records['message'])
        <div class="p-4 mb-8 rounded-lg shadow-lg bg-red-500 text-white">
            {!! $records['message'] !!}
        </div>
    @endif

    @include('domain_records_form')

</div>

<div class="mt-8">

    @include('domain_records_table')

</div>

</div>
{{-- END PAGE CONTAINER --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/2.3.0/alpine.js" integrity="sha512-nIwdJlD5/vHj23CbO2iHCXtsqzdTTx3e3uAmpTm4x2Y8xCIFyWu4cSIV8GaGe2UNVq86/1h9EgUZy7tn243qdA==" crossorigin="anonymous"></script>