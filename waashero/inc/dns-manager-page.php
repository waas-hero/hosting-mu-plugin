<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.0.4/tailwind.min.css" integrity="sha512-paVHVRRhdoOu1nOXKnqDC1Vka0nh7FAmU3nsM4n2FKxOQTeF6crMdMfkVvEsuaOXZ6oEAVL5+wLbQcule/Xdag==" crossorigin="anonymous" />

<div class="mt-4">

    <h1 class="text-gray-500 text-xl">
        
        <?php esc_html_e( 'Welcome to my custom admin page.' );?>

    </h1>

    <div class="flex flex-col max-w-7xl justify-center mx-auto p-7" >

    <div class="rounded-lg shadow bg-white max-w-screen overflow-x-scroll">
        <div class="flex flex-col">
            <div class="table align-middle min-w-full">
                <div class="table-row divide-x divide-gray-200">
         
                    <div class="relative table-cell h-12 overflow-hidden align-top">
                    
                        <button wire:click.prefetch="sortBy('type')" class="w-full h-full px-6 py-3 border-b border-gray-200 bg-gray-50 text-center text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider flex items-center focus:outline-none flex justify-center">
                            <span class="inline ">{{ __('Type') }}</span>
                        </button>

                    </div>

                    <div class="relative table-cell h-12 overflow-hidden align-top">
                    
                        <button wire:click.prefetch="sortBy('hostname')" class="w-full h-full px-6 py-3 border-b border-gray-200 bg-gray-50 text-center text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider flex items-center focus:outline-none flex justify-center">
                            <span class="inline ">{{ __('Hostname') }}</span>
                        </button>

                    </div>


                    <div class="relative table-cell h-12 overflow-hidden align-top">
                    
                        <button wire:click.prefetch="sortBy('value')" class="w-full h-full px-6 py-3 border-b border-gray-200 bg-gray-50 text-center text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider flex items-center focus:outline-none flex justify-center">
                            <span class="inline ">{{ __('Value') }}</span>
                        </button>

                    </div>

                    <div class="relative table-cell h-12 overflow-hidden align-top">
                    
                        <button wire:click.prefetch="sortBy('ttl')" class="w-full h-full px-6 py-3 border-b border-gray-200 bg-gray-50 text-center text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider flex items-center focus:outline-none flex justify-center">
                            <span class="inline ">{{ __('TTL (seconds)') }}</span>
                        </button>

                    </div>

                    <div class="relative table-cell h-12 overflow-hidden align-top">
                    
                        <button class="w-full h-full px-6 py-3 border-b border-gray-200 bg-gray-50 text-center text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider flex items-center focus:outline-none flex justify-center">
                            <span class="inline ">{{ __('Actions') }}</span>
                        </button>

                    </div>

                </div>
          
                @if( $records !== null && is_array($records->items()) )
                    @foreach ($records->items() as $key => $record)
                    <div class="table-row p-1 divide-x divide-gray-100">
                            <div class="px-6 py-2 whitespace-no-wrap text-sm leading-5 text-gray-900 table-cell text-left">
                                {{ $record['type'] }}
                            </div>
                            <div class="px-6 py-2 whitespace-no-wrap text-sm leading-5 text-gray-900 table-cell text-left">
                                {{ $record['hostname']}}
                            </div>
                            <div class="px-6 py-2 whitespace-no-wrap text-sm leading-5 text-gray-900 table-cell text-left">
                                {{ $record['value'] }}
                            </div>
                            <div class="px-6 py-2 whitespace-no-wrap text-sm leading-5 text-gray-900 table-cell text-left">
                                {{ $record['ttl'] }}
                            </div>
         
                            
                            <div class="px-6 py-2 whitespace-no-wrap text-sm leading-5 text-gray-900 table-cell text-left">
                               
                                @if( !empty($confirming) && $confirming['id'] == $record['id'] && $confirming['value'] == $record['value'])

                                <button wire:click="$emit('deleteRecord', '{{$record['hostname']}}', '{{$record['type']}}', '{{$record['ttl']}}', '{{$record['value']}}')" class="btn rounded text-white btn bg-red-500">Confirm Delete</button>

                                    <button wire:click="resetDelete" class="btn rounded  text-white btn bg-red-500">{{__('Reset')}}</button>

                                @elseif($deleting === $record['value'])
                                    <button disabled class='btn text-white bg-red-500'>Deleting</button>
                                @else

                                    @if($record['type'] !== 'NS')
                                        <button wire:click="confirmDelete('{{$record['id']}}', '{{$record['value']}}')" class="btn rounded text-white bg-red-500">Delete</button>
                                    @endif 
                            
                                @endif

                            </div>
                            

                        </div>
                    @endforeach

                @else 
                    <p class="p-3 text-lg text-teal-600">
                        {{__('No data was found.')}}
                    </p>
                @endif

                
        </div>
        <div class="rounded-lg rounded-t-none max-w-screen rounded-lg border-b border-gray-200 bg-white w-full">
            <div class="p-2 sm:flex items-center justify-between w-full">
              
                    <div class="my-2 sm:my-0 flex items-center justify-start">
                        <select name="perPage" class="mt-1 form-select block w-full pl-3 pr-10 py-1 rounded text-base leading-6 border-gray-300 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 sm:text-sm sm:leading-5" wire:model="perPage">
                            <option value="1">1</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="99999999">All</option>
                        </select>
                    </div>
    
                    <div class="my-4 sm:my-0">
    
                        <div class="lg:flex justify-center">
                            <span>{{ $records->links() }}</span>
                        </div>
                    </div>
    
                    <div class="flex justify-end text-gray-600">
                        Results {{ $records->firstItem() }} - {{ $records->lastItem() }} of
                        {{ $records->total() }}
                    </div>
          
                </div>
            </div>
        </div>
    </div>

    </div>

</div>