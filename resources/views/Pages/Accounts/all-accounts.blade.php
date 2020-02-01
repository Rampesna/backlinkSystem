@extends('Layouts.master')
@section('site_title', 'Backlink Satış')


@section('content')



    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="body">
                    <div class="table-responsive">
                        <table id="allAccounts" class="table table-hover js-basic-example dataTable table-custom spacing5">
                            <thead>
                            <tr>
                                <th>Ad Soyad</th>
                                <th>Kullanıcı Adı</th>
                                <th>E-posta</th>
                                <th>Telefon</th>
                                <th>Kredi</th>
                                <th>Düzenle</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>Ad Soyad</th>
                                <th>Kullanıcı Adı</th>
                                <th>E-posta</th>
                                <th>Telefon</th>
                                <th>Kredi</th>
                                <th>Düzenle</th>
                            </tr>
                            </tfoot>
                            <tbody>

                            @foreach($allAccounts as $account)
                            <tr>
                                <td>{{$account->name_surname}}</td>
                                <td>{{$account->username}}</td>
                                <td>{{$account->email}}</td>
                                <td>{{"+90 (".substr($account->phone,0,3).") ".substr($account->phone,3,3)." ".substr($account->phone,6,4)}}</td>
                                <td style="color: #00aa00">{{$account->balance}} ₺</td>
                                <td>
                                    <a href="{{route('account-detail',\Illuminate\Support\Facades\Crypt::encrypt($account->id))}}"><i style="color: #00A6C7" class="fa fa-eye"></i></a>&nbsp;&nbsp;
                                    <a><i style="color: #00A6C7" class="fa fa-edit"></i></a>&nbsp;&nbsp;
                                    <a><i style="color: #00A6C7" class="fa fa-trash"></i></a>&nbsp;&nbsp;
                                </td>
                            </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                $('#allAccounts').DataTable({
                    language: {
                        info: "_TOTAL_ Kayıttan _START_ - _END_ Arasındaki Kayıtlar Gösteriliyor.",
                        infoEmpty: "Gösterilecek Hiç Kayıt Yok.",
                        loadingRecords: "Kayıtlar Yükleniyor.",
                        zeroRecords: "Tablo Boş",
                        search: "Arama:",
                        infoFiltered: "(Yoplam _MAX_ Kayıttan Filtrelenenler)",
                        lengthMenu: "Sayfa Başı _MENU_ Kayıt Göster",
                        /*
                        buttons: {
                            copyTitle: "Panoya Kopyalandı.",
                            copySuccess:"Panoya %d Satır Kopyalandı",
                            copy: "Kopyala",
                            print: "Yazdır",
                        },
            */
                        paginate: {
                            first: "İlk",
                            previous: "Önceki",
                            next: "Sonraki",
                            last: "Son"
                        },
                    },
                    dom: 'frtipl',

                    /*
                    buttons: [
                        'copy', 'excel', 'pdf', 'print'
                    ],
                    */
                    responsive: true
                });
            </script>
@stop
