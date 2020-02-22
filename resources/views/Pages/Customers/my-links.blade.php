@extends('Layouts.master')
@section('site_title', 'Backlink Satış')

@section('custom-header')
<style>
    @-webkit-keyframes rotating /* Safari and Chrome */ {
        from {
            -webkit-transform: rotate(0deg);
            -o-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        to {
            -webkit-transform: rotate(360deg);
            -o-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }
    @keyframes rotating {
        from {
            -ms-transform: rotate(0deg);
            -moz-transform: rotate(0deg);
            -webkit-transform: rotate(0deg);
            -o-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        to {
            -ms-transform: rotate(360deg);
            -moz-transform: rotate(360deg);
            -webkit-transform: rotate(360deg);
            -o-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }
    .rotating {
        -webkit-animation: rotating 2s linear infinite;
        -moz-animation: rotating 2s linear infinite;
        -ms-animation: rotating 2s linear infinite;
        -o-animation: rotating 2s linear infinite;
        animation: rotating 2s linear infinite;
    }
</style>
@endsection

@section('content')

    @if(isset($returnArray))

        @if(count($returnArray) > 0)
            <div class="col-12">
                <div class="table-responsive">
                    <table id="allSites" class="table header-border table-hover table-custom spacing7">
                        <thead>
                        <tr>
                            <th><strong>Link Alınan Site</strong></th>
                            <th><strong>Alınan Link URL</strong></th>
                            <th><strong>Kelimeler</strong></th>
                            <th class="text-center"><strong>Eklenme Durumu</strong></th>
                            <th class="text-center"><strong>Link Durumu</strong></th>
                            <th class="text-center"><strong>Düzenle</strong></th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($returnArray as $site)
                            <tr>
                                <td>{{$site["site_url"]}}</td>
                                <td><a class="text-info" target="_blank" href="http://{{$site["link_url"]}}">{{$site["link_url"]}}</a></td>
                                <td>{{$site["keywords"]}}</td>
                                <td class="text-center">
                                    @if($site["is_added"] == "1")
                                        <span style="color: green">Eklendi</span>
                                    @else
                                        <span style="color: orange">Bekliyor</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($site["is_added"] == "0")
                                        <button disabled purchase="{{$site["purchase_id"]}}" class="btn btn-outline-secondary purchaseControl">Kontrol Et</button>
                                        <i style="color: orange" id="loading_{{$site["purchase_id"]}}" class="fa fa-spinner rotating"></i>
                                        <i id="purchase_i_id_{{$site["purchase_id"]}}" class="fa fa-lg"></i>
                                    @elseif($site["is_added"] == "1")
                                        <button purchase="{{$site["purchase_id"]}}" class="btn btn-outline-info purchaseControl">Kontrol Et</button>
                                        <i style="color: orange" id="loading_{{$site["purchase_id"]}}" class="fa fa-spinner rotating"></i>
                                        <i id="purchase_i_id_{{$site["purchase_id"]}}" class="fa fa-lg"></i>
                                    @endif

                                </td>
                                <td class="text-center">
                                    <a href="{{route('edit-my-link',$site["purchase_id"])}}" class="btn btn-outline-warning">Düzenle</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="col-lg-12">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Link Bulunamadı!</h5>
                        <p class="card-text">Görünüşe Göre Site(leri)niz İçin Henüz Hiç Link Satın Almamışsınız. Linkleri Görmek ve Satın Almak İçin Aşağıdaki Butona Tıklayabilirsiniz.</p>
                        <a href="{{route('customer-all-links')}}" class="btn btn-info btn-block">Link Satın Al</a>
                    </div>
                </div>
            </div>
        @endif

    @endif

@stop

@section('custom-header')
    <link rel="stylesheet" href="{{ asset('assets/vendor/c3/c3.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/jquery-datatable/fixedeader/dataTables.fixedcolumns.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/jquery-datatable/fixedeader/dataTables.fixedheader.bootstrap4.min.css') }}">
@stop

@section('page-script')

    <script src="{{ asset('assets/bundles/datatablescripts.bundle.js') }}"></script>
    <script src="{{ asset('assets/vendor/jquery-datatable/buttons/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/jquery-datatable/buttons/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/jquery-datatable/buttons/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/jquery-datatable/buttons/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/jquery-datatable/buttons/buttons.print.min.js') }}"></script>

    <script src="{{ asset('assets/js/pages/tables/jquery-datatable.js') }}"></script>

    <script src="{{ asset('assets/bundles/c3.bundle.js') }}"></script>
    <script src="{{ asset('assets/bundles/mainscripts.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/index.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.rotating').hide();
            $(".purchaseControl").click(function(e) {
                $(this).hide();
                var p_id = $(this).attr('purchase');
                $.ajax({
                    type: "get",
                    url: "{{route('link-control')}}",
                    data: {
                        purchase_id: $(this).attr('purchase'),
                    },
                    beforeSend: loadStart,
                    complete: loadStop,
                    success: function(result) {
                        if(result == "true"){
                            $("#purchase_i_id_"+p_id).addClass('fa-check');
                            $("#purchase_i_id_"+p_id).css({"color": "green"})
                        }else{
                            $("#purchase_i_id_"+p_id).addClass('fa-close');
                            $("#purchase_i_id_"+p_id).css({"color": "red"})
                        }
                    },
                    error: function(result) {
                        $("#purchase_i_id_"+p_id).addClass('fa-close');
                        $("#purchase_i_id_"+p_id).css({"color": "red"})
                    }
                });

                function loadStart() {
                    $('#loading_'+p_id).show();
                }
                function loadStop() {
                    $('#loading_' + p_id).hide();
                }
            });
        });
    </script>
@stop