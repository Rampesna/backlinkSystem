<?php

namespace App\Http\Controllers;

use App\Helpers\LogSystem;
use App\Models\LinksTableModel;
use App\Models\PremiumSitesTableModel;
use App\Models\PurchasedLinksTableModel;
use App\Models\SalesTableModel;
use App\Models\TicketMessagesTableModel;
use App\Models\TicketsTableModel;
use App\Models\UserSitesTableModel;
use App\Models\UsersTableModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class CustomerController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function myAccount()
    {
        $transaction = "CustomerController@myAccount";
        $detail = "Müşteri Profil Sayfasını Görüntüledi";
        LogSystem::createNewLog($transaction, $detail);
        return view('Pages.Customers.my-account');
    }

    public function mySites()
    {
        $transaction = "CustomerController@mySites";
        $detail = "Müşteri Kendi Sitelerini Görüntüledi";
        LogSystem::createNewLog($transaction, $detail);
        $user = Auth::user();
        $mySites = UserSitesTableModel::where('user_id', $user->id)->where('is_delete', 0)->get();
        return view('Pages.Customers.my-sites', compact('mySites'));
    }

    public function deleteSite($id)
    {
        $deletedSite = UserSitesTableModel::where('id', $id)->first();
        $deletedSite->is_delete = 1;
        $deletedSite->save();

        $transaction = "CustomerController@deleteSite";
        $detail = "Müşteri Kendi Sitesini Sildi. Sildiği Site URL : " . $deletedSite->url;
        LogSystem::createNewLog($transaction, $detail);

        PurchasedLinksTableModel::where('site_id', $id)->delete();

        return redirect()->route('my-sites');
    }

    public function addSite()
    {
        $transaction = "CustomerController@addSite";
        $detail = "Müşteri Yeni Site Ekleme Formunu Açtı";
        LogSystem::createNewLog($transaction, $detail);

        return view('Pages.Customers.add-site');
    }

    public function addSiteControl(Request $request)
    {
        if (
        is_null($request->site_url)
        ) {
            $errorMessage = "Eksik Bilgiler Mevcut! Lütfen Kontrol Ederek Tekrar Deneyin.";
            return view('Pages.Customers.add-site', compact('errorMessage'));
        } else {
            $url = trim($request->site_url, '/');
            if (!preg_match('#^http(s)?://#', $url)) {
                $url = 'http://' . $url;
            }
            $urlParts = parse_url($url);
            $lastUrl = preg_replace('/^www\./', '', $urlParts['host']);
            if (UserSitesTableModel::where('url', $lastUrl)->count() > 0) {
                $errorMessage = "Bu Site Sistemde Zaten Mevcut. Lütfen Kontrol Ederek Tekrar Deneyin!";
                return view('Pages.Customers.add-site', compact('errorMessage'));
            } else {
                $returnArray = [
                    "url" => $lastUrl,
                    "status" => true
                ];

                $transaction = "CustomerController@addSiteControl";
                $detail = "Müşteri '".$lastUrl."' Sitesinin Eklenebilirliğini Kontrol Etti";
                LogSystem::createNewLog($transaction, $detail);

                return view('Pages.Customers.add-site', compact('returnArray'));
            }
        }

    }

    public function addSitePost(Request $request)
    {
        if (is_null($request->site_url)) {
            $errorMessage = "Bir Hata Oluştu. Lütfen Bir Süre Sonra Tekrar Deneyin!";
            return view('Pages.Customers.add-site', compact('errorMessage'));
        } else {
            $user = Auth::user();
            $newSite = new UserSitesTableModel;
            $newSite->url = $request->site_url;
            $newSite->user_id = $user->id;
            $newSite->save();

            $transaction = "CustomerController@addSitePost";
            $detail = "Müşteri '".$request->site_url."' Sitesini Ekledi";
            LogSystem::createNewLog($transaction, $detail);

            return redirect()->route('my-sites');
        }
    }

    public function editSite($id)
    {
        $getSite = UserSitesTableModel::find($id);

        $transaction = "CustomerController@editSite";
        $detail = "Müşteri '".$getSite->url."' Sitesini Düzenleme Sayfasını Açtı";
        LogSystem::createNewLog($transaction, $detail);

        return view('Pages.Customers.edit-site', compact('getSite'));
    }

    public function updateSite(Request $request)
    {
        if (is_null($request->site_id)) {
            return abort(403);
        } else {
            $getSite = UserSitesTableModel::find($request->site_id);
            $before = $getSite->url;
            if (is_null($request->site_url)) {
                $errorMessage = "Eksik Bilgi Mevcut! Lütfen Konrol Ederek Tekrar Deneyin.";
                return view('Pages.Customers.edit-site', compact('getSite', 'errorMessage'));
            } else {
                $url = trim($request->site_url, '/');
                if (!preg_match('#^http(s)?://#', $url)) {
                    $url = 'http://' . $url;
                }
                $urlParts = parse_url($url);
                $cleanLink = preg_replace('/^www\./', '', $urlParts['host']);
                $getSite->url = $cleanLink;
                $getSite->save();

                $transaction = "CustomerController@updateSite";
                $detail = "Müşteri '".$before."' Sitesini ".$cleanLink." Olarak Güncelledi";
                LogSystem::createNewLog($transaction, $detail);

                return redirect()->route('my-sites');
            }
        }
    }

    public function myLinks()
    {
        $user = Auth::user();
        $purchasedLinks = PurchasedLinksTableModel::where('user_id', $user->id)->get();
        $returnArray = [];
        foreach ($purchasedLinks as $purchasedLink) {
            $getLink = LinksTableModel::select('url')->where('id', $purchasedLink->link_id)->first();
            $getSite = UserSitesTableModel::select('url')->where('id', $purchasedLink->site_id)->first();
            $returnArray[] = [
                "purchase_id" => $purchasedLink->id,
                "site_url" => $getSite->url,
                "link_url" => $getLink->url,
                "keywords" => $purchasedLink->keyword,
                "is_added" => $purchasedLink->is_added
            ];
        }

        $transaction = "CustomerController@myLinks";
        $detail = "Müşteri Satın Aldığı Linkleri Görüntüledi";
        LogSystem::createNewLog($transaction, $detail);

        return view('Pages.Customers.my-links', compact('returnArray'));
    }

    public function editLink($id)
    {
        $getPurchasedLink = PurchasedLinksTableModel::find($id);
        $getSite = LinksTableModel::find($getPurchasedLink->site_id);

        $transaction = "CustomerController@editLink";
        $detail = "Müşteri '".$getSite->url."' Sitesinden Aldığı Linki Düzenleme Sayfasını Açtı";
        LogSystem::createNewLog($transaction, $detail);

        return view('Pages.Customers.edit-link', compact('getPurchasedLink'));
    }

    public function updateLink(Request $request)
    {
        if (is_null($request->purchase_id)) {
            return abort(404);
        } else if (is_null($request->keyword)) {
            $errorMessage = "Eksik Bilgiler Mevcut! Lütfen Kotrol Ederek Tekrar Deneyin.";
            $getPurchasedLink = PurchasedLinksTableModel::find($request->purchase_id);
            return view('Pages.Customers.edit-link', compact('getPurchasedLink', 'errorMessage'));
        } else {
            $getPurchase = PurchasedLinksTableModel::find($request->purchase_id);
            $getPurchase->keyword = $request->keyword;
            $getPurchase->save();

            $getSite = LinksTableModel::find($getPurchase->site_id);

            $transaction = "CustomerController@updateLink";
            $detail = "Müşteri '".$getSite->url."' Sitesinden Aldığı Linkin Kelimelerini '".$request->keyword."' Olarak Güncelledi";
            LogSystem::createNewLog($transaction, $detail);

            return redirect()->route('my-links');
        }
    }

    public function linkControl(Request $request)
    {
        $purchasedLink = PurchasedLinksTableModel::find($request->purchase_id);
        $getLink = LinksTableModel::select('url')->where('id', $purchasedLink->link_id)->first();
        $getSite = UserSitesTableModel::select('url')->where('id', $purchasedLink->site_id)->first();
        try {
            $control = file_get_contents("http://" . $getLink->url);
            if (strpos($control, $getSite->url)) {
                echo "true";
            } else {
                echo "false";
            }
        } catch (\Exception $e) {
            echo "false";
        }
    }

    public function allLinks()
    {
        $allLinks = LinksTableModel::where('is_delete', 0)->get();

        $transaction = "CustomerController@allLinks";
        $detail = "Müşteri Tüm Linkler Sayfasını Görüntüledi";
        LogSystem::createNewLog($transaction, $detail);

        return view('Pages.Customers.all-links', compact('allLinks'));
    }

    public function buyLink($linkID)
    {
        $user = Auth::user();
        $link = LinksTableModel::where('id', $linkID)->first();
        $mySites = UserSitesTableModel::where('user_id', $user->id)->get();

        $transaction = "CustomerController@buyLink";
        $detail = "Müşteri '".$link->url."' Sitesinden Link Alma Ekranını Görüntüledi";
        LogSystem::createNewLog($transaction, $detail);

        return view('Pages.Customers.buy-link', compact('mySites', 'link'));
    }

    public function buyLinkPost(Request $request)
    {
        $user = Auth::user();
        $link = LinksTableModel::where('id', $request->link_id)->first();
        $purchaseControl = PurchasedLinksTableModel::
        where('site_id', $request->site_id)->
        where('link_id', $request->link_id)->
        where('user_id', $user->id)->
        first();

        if (is_null($purchaseControl)) {
            $newPurchase = new PurchasedLinksTableModel;
            $newPurchase->site_id = $request->site_id;
            $newPurchase->user_id = $user->id;
            $newPurchase->link_id = $link->id;
            $newPurchase->keyword = $request->keywords;
            if ($link->add_type == 1) {
                $newPurchase->is_added = 1;
            } else {
                $newPurchase->is_added = 0;
            }
            $newPurchase->is_reported = 0;
            $newPurchase->is_seen = 0;
            $newPurchase->save();

            $getSite = UserSitesTableModel::find($request->site_id);

            $getUser = UsersTableModel::where('id', $user->id)->first();
            $getUser->balance = $getUser->balance - $link->price;
            $getUser->save();

            $transaction = "CustomerController@buyLinkPost";
            $detail = "Müşteri '".$link->url."' Sitesinden Kendi '".$getSite->url."' Sitesine '".$request->keywords."' Kelimelerinde Link Satın Aldı";
            LogSystem::createNewLog($transaction, $detail);

            return redirect()->route('my-links');
        } else {
            $errorMessage = "Bu Site İçin Bu Link Zaten Satın Alınmış! Lütfen Bu Linki Başka Bir Siteniz İçin Satın Alın veya Satın Aldığım Linkler Bölümüne Giderek Düzenleyin.";
            $mySites = UserSitesTableModel::where('user_id', $user->id)->get();
            return view('Pages.Customers.buy-link', compact('errorMessage', 'mySites', 'link'));
        }
    }

    public function buyCredit()
    {
        $user = Auth::user();
        $control = SalesTableModel::where('user_id', $user->id)->where('status', 'waiting')->first();
        if (is_null($control)) {
            $transaction = "CustomerController@buyCredit";
            $detail = "Müşteri Kredi Başvurusu Sayfasını Görüntüledi";
            LogSystem::createNewLog($transaction, $detail);

            return view('Pages.Customers.buy-credit');
        } else {
            $errorMessage = "Zaten Onay Bekleyen Bir Ödeme Bildiriminiz Bulunmakta! İşlem Sonlandırılana Kadar Yeni Bir Ödeme Bildiriminde Bulunamazsınız.";
            return view('Pages.Customers.buy-credit', compact('errorMessage'));
        }
    }

    public function buyCreditPost(Request $request)
    {
        $user = Auth::user();
        $newSale = new SalesTableModel;
        $newSale->user_id = $user->id;
        $newSale->iban = $request->iban;
        $newSale->name_surname = $request->name_surname;
        $newSale->order_id = Str::random(64);
        $newSale->amount = $request->amount;
        $newSale->status = "waiting";
        $newSale->is_reported = 0;
        $newSale->is_seen = 0;
        $newSale->save();

        $transaction = "CustomerController@buyCreditPost";
        $detail = "Müşteri ".$request->amount." Miktarlık Kredi Başvurusu Yaptı";
        LogSystem::createNewLog($transaction, $detail);

        return redirect()->route('buy-credit');
    }

    public function addTicket()
    {
        $transaction = "CustomerController@addTicket";
        $detail = "Müşteri Destek Talebinde Bulunma Sayfasını Açtı";
        LogSystem::createNewLog($transaction, $detail);

        return view('Pages.Customers.add-ticket');
    }

    public function addTicketPost(Request $request)
    {
        $user = Auth::user();
        if (
            is_null($request->title) ||
            is_null($request->priority) ||
            is_null($request->description)
        ) {
            $errorMessage = "Eksik Bilgiler Mevcut! Lütfen Kontrol Ederek Tekrar Deneyin.";
            return view('Pages.Customers.add-ticket', compact('errorMessage'));
        } else {
            $newTicket = new TicketsTableModel;
            $newTicket->user_id = $user->id;
            $newTicket->title = $request->title;
            $newTicket->description = $request->description;
            $newTicket->priority = intval($request->priority);
            $newTicket->status = "pending";
            $newTicket->save();

            $transaction = "CustomerController@addTicketPost";
            $detail = "Müşteri Destek Talebinde Bulundu";
            LogSystem::createNewLog($transaction, $detail);

            return redirect()->route('my-all-tickets', 1);
        }
    }

    public function allTickets($page = 1)
    {
        $user = Auth::user();
        $countControl = TicketsTableModel::where('user_id', $user->id)->count();
        if (($countControl <= (($page - 1) * 10)) && $countControl > 0) {
            return redirect()->route('my-all-tickets', 1);
        } else {
            if (is_null($page) || $page == 1) {
                $allTickets = TicketsTableModel::where('user_id', $user->id)->orderBy('updated_at', 'DESC')->limit(10)->get();
            } else {
                $allTickets = TicketsTableModel::where('user_id', $user->id)->skip(($page - 1) * 10)->take(10)->orderBy('updated_at', 'DESC')->get();
            }
            $allTicketsCount = TicketsTableModel::where('user_id', $user->id)->count();
            $respondedCount = TicketsTableModel::where('user_id', $user->id)->where('status', 'responded')->count();
            $resolvedCount = TicketsTableModel::where('user_id', $user->id)->where('status', 'resolved')->count();
            $pendingCount = TicketsTableModel::where('user_id', $user->id)->where('status', 'pending')->count();
            $type = "all";

            $transaction = "CustomerController@addTicketPost";
            $detail = "Müşteri Tüm Destek Taleplerini Görüntüledi";
            LogSystem::createNewLog($transaction, $detail);

            return view('Pages.Customers.my-tickets', compact(
                'allTickets',
                'allTicketsCount',
                'respondedCount',
                'resolvedCount',
                'pendingCount',
                'page',
                'type'
            ));
        }
    }

    public function pendingTickets($page = 1)
    {
        $user = Auth::user();
        $countControl = TicketsTableModel::where('user_id', $user->id)->where('status', 'pending')->count();
        if (($countControl <= (($page - 1) * 10)) && $countControl > 0) {
            return redirect()->route('my-pending-tickets', 1);
        } else {
            if (is_null($page) || $page == 1) {
                $allTickets = TicketsTableModel::where('user_id', $user->id)->where('status', 'pending')->orderBy('updated_at', 'DESC')->limit(10)->get();
            } else {
                $allTickets = TicketsTableModel::where('user_id', $user->id)->where('status', 'pending')->skip(($page - 1) * 10)->take(10)->orderBy('updated_at', 'DESC')->get();
            }
            $allTicketsCount = TicketsTableModel::where('user_id', $user->id)->count();
            $respondedCount = TicketsTableModel::where('user_id', $user->id)->where('status', 'responded')->count();
            $resolvedCount = TicketsTableModel::where('user_id', $user->id)->where('status', 'resolved')->count();
            $pendingCount = TicketsTableModel::where('user_id', $user->id)->where('status', 'pending')->count();
            $type = "all";

            $transaction = "CustomerController@addTicketPost";
            $detail = "Müşteri Bekleyen Destek Taleplerini Görüntüledi";
            LogSystem::createNewLog($transaction, $detail);

            return view('Pages.Customers.my-tickets', compact(
                'allTickets',
                'allTicketsCount',
                'respondedCount',
                'resolvedCount',
                'pendingCount',
                'page',
                'type'
            ));
        }
    }

    public function respondedTickets($page = 1)
    {
        $user = Auth::user();
        $countControl = TicketsTableModel::where('user_id', $user->id)->where('status', 'responded')->count();
        if (($countControl <= (($page - 1) * 10)) && $countControl > 0) {
            return redirect()->route('my-responded-tickets', 1);
        } else {
            if (is_null($page) || $page == 1) {
                $allTickets = TicketsTableModel::where('user_id', $user->id)->where('status', 'responded')->orderBy('updated_at', 'DESC')->limit(10)->get();
            } else {
                $allTickets = TicketsTableModel::where('user_id', $user->id)->where('status', 'responded')->skip(($page - 1) * 10)->take(10)->orderBy('updated_at', 'DESC')->get();
            }
            $allTicketsCount = TicketsTableModel::where('user_id', $user->id)->count();
            $respondedCount = TicketsTableModel::where('user_id', $user->id)->where('status', 'responded')->count();
            $resolvedCount = TicketsTableModel::where('user_id', $user->id)->where('status', 'resolved')->count();
            $pendingCount = TicketsTableModel::where('user_id', $user->id)->where('status', 'pending')->count();
            $type = "all";

            $transaction = "CustomerController@addTicketPost";
            $detail = "Müşteri Cevaplanan Destek Taleplerini Görüntüledi";
            LogSystem::createNewLog($transaction, $detail);

            return view('Pages.Customers.my-tickets', compact(
                'allTickets',
                'allTicketsCount',
                'respondedCount',
                'resolvedCount',
                'pendingCount',
                'page',
                'type'
            ));
        }
    }

    public function resolvedTickets($page = 1)
    {
        $user = Auth::user();
        $countControl = TicketsTableModel::where('user_id', $user->id)->where('status', 'resolved')->count();
        if (($countControl <= (($page - 1) * 10)) && $countControl > 0) {
            return redirect()->route('my-resolved-tickets', 1);
        } else {
            if (is_null($page) || $page == 1) {
                $allTickets = TicketsTableModel::where('user_id', $user->id)->where('status', 'resolved')->orderBy('updated_at', 'DESC')->limit(10)->get();
            } else {
                $allTickets = TicketsTableModel::where('user_id', $user->id)->where('status', 'resolved')->skip(($page - 1) * 10)->take(10)->orderBy('updated_at', 'DESC')->get();
            }
            $allTicketsCount = TicketsTableModel::where('user_id', $user->id)->count();
            $respondedCount = TicketsTableModel::where('user_id', $user->id)->where('status', 'responded')->count();
            $resolvedCount = TicketsTableModel::where('user_id', $user->id)->where('status', 'resolved')->count();
            $pendingCount = TicketsTableModel::where('user_id', $user->id)->where('status', 'pending')->count();
            $type = "all";

            $transaction = "CustomerController@addTicketPost";
            $detail = "Müşteri Çözülen Destek Taleplerini Görüntüledi";
            LogSystem::createNewLog($transaction, $detail);

            return view('Pages.Customers.my-tickets', compact(
                'allTickets',
                'allTicketsCount',
                'respondedCount',
                'resolvedCount',
                'pendingCount',
                'page',
                'type'
            ));
        }
    }

    public function showTicket($id)
    {
        $ticket = TicketsTableModel::find($id);
        $ticketMessages = TicketMessagesTableModel::where('ticket_id', $id)->orderBy('created_at', 'DESC')->get();
        $ticketUser = UsersTableModel::where('id', $ticket->user_id)->first();

        $transaction = "CustomerController@addTicketPost";
        $detail = "Müşteri ".$ticket->id."ID'li Destek Talebinin İçeriğine Baktı";
        LogSystem::createNewLog($transaction, $detail);

        return view('Pages.Tickets.show-ticket', compact(
            'ticket',
            'ticketMessages',
            'ticketUser'
        ));
    }

    public function myPremiumSites()
    {
        $user = Auth::user();
        $myPremiumSites = PremiumSitesTableModel::where('user_id', $user->id)->get();
        return view('Pages.Customers.my-premium-sites', compact('myPremiumSites'));
    }

    public function addPremiumSiteForm()
    {
        $user = Auth::user();
        $mySites = UserSitesTableModel::where('user_id',$user->id)->get();
        $controlledSites = [];
        foreach ($mySites as $mySite){
            $control = PremiumSitesTableModel::where('site_id',$mySite->id)->first();
            if(is_null($control)){
                $controlledSites[] = [
                    'site_id' => $mySite->id,
                    'site_url' => $mySite->url
                ];
            }
        }
        return view('Pages.Customers.add-premium-site',compact('controlledSites'));
    }

    public function addPremiumSitePost(Request $request)
    {
        return $request->premium_site_id;
    }

}
