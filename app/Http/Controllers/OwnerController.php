<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class OwnerController extends Controller
{
    private function owner(): void { abort_unless(Auth::check() && Auth::user()->role === 'admin', 403); }

    public function dashboard()
    {
        $this->owner();
        $today = today();
        $month = now()->month;
        $year = now()->year;
        $stats = [
            'Penjualan hari ini' => DB::table('orders')->whereDate('created_at',$today)->whereNotIn('status',['cancelled'])->sum('total'),
            'Pendapatan dibayar' => DB::table('orders')->where('payment_status','paid')->sum('total'),
            'Pesanan aktif' => DB::table('orders')->whereIn('status',['pending','confirmed','processing','packed','ready'])->count(),
            'Total produk aktif' => DB::table('products')->where('is_active',1)->count(),
            'Total stok' => DB::table('products')->sum('stock'),
            'Batch kedaluwarsa' => DB::table('product_batches')->whereDate('expiry_date','<',$today)->where('quantity','>',0)->count(),
            'Batch segera expired' => DB::table('product_batches')->whereBetween('expiry_date',[$today,now()->addDays(30)])->where('quantity','>',0)->count(),
            'Retur menunggu' => DB::table('returns')->where('status','pending')->count(),
        ];
        $latestOrders = DB::table('orders')->join('users','users.id','=','orders.user_id')
            ->select('orders.id','orders.order_number','orders.total','orders.status','orders.payment_status','orders.created_at','users.name as buyer')
            ->latest('orders.created_at')->limit(7)->get();
        $expiry = DB::table('product_batches')->join('products','products.id','=','product_batches.product_id')
            ->select('product_batches.*','products.name as product_name','products.sku')
            ->where('product_batches.quantity','>',0)->whereDate('expiry_date','<=',now()->addDays(30))
            ->orderBy('expiry_date')->limit(8)->get();
        $activity = DB::table('activity_logs')->leftJoin('users','users.id','=','activity_logs.user_id')
            ->select('activity_logs.*','users.name as actor')->latest('activity_logs.created_at')->limit(8)->get();
        $sales = DB::table('orders')->selectRaw('DATE(created_at) as day, SUM(total) as total')
            ->where('created_at','>=',now()->subDays(6)->startOfDay())->whereNotIn('status',['cancelled'])
            ->groupByRaw('DATE(created_at)')->orderBy('day')->get();
        return view('owner.dashboard',compact('stats','latestOrders','expiry','activity','sales'));
    }

    public function reportSales(Request $r)
    {
        $this->owner();
        $from=$r->input('from',now()->startOfMonth()->toDateString());
        $to=$r->input('to',today()->toDateString());
        $rows=DB::table('orders')->join('users','users.id','=','orders.user_id')
            ->select('orders.*','users.name as buyer')->whereDate('orders.created_at','>=',$from)->whereDate('orders.created_at','<=',$to)
            ->orderByDesc('orders.created_at')->paginate(20)->withQueryString();
        $totals=['Transaksi'=>$rows->total(),'Omzet'=>DB::table('orders')->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->whereNotIn('status',['cancelled'])->sum('total'),
            'Pembayaran diterima'=>DB::table('orders')->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('payment_status','paid')->sum('total')];
        return view('owner.sales',compact('rows','from','to','totals'));
    }


    public function finance(Request $r)
    {
        $this->owner();
        $from=$r->input('from',now()->startOfMonth()->toDateString());
        $to=$r->input('to',today()->toDateString());
        $base=DB::table('orders')->where('payment_status','paid')->whereNotIn('status',['cancelled'])
            ->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to);
        $revenue=(clone $base)->sum('total');
        $cost=DB::table('order_details')->join('orders','orders.id','=','order_details.order_id')
            ->join('products','products.id','=','order_details.product_id')
            ->where('orders.payment_status','paid')->whereNotIn('orders.status',['cancelled'])
            ->whereDate('orders.created_at','>=',$from)->whereDate('orders.created_at','<=',$to)
            ->sum(DB::raw('order_details.quantity * products.purchase_price'));
        $grossProfit=$revenue-$cost;
        return view('owner.finance',compact('from','to','revenue','cost','grossProfit'));
    }

    public function generic(string $type, Request $r)
    {
        $this->owner();
        $configs = [
            'products'=>['title'=>'Laporan Produk','query'=>fn()=>DB::table('products')->leftJoin('categories','categories.id','=','products.category_id')->select('products.name','products.sku','categories.name as category','products.price','products.purchase_price','products.stock','products.is_active')->orderBy('products.name'),'columns'=>['name'=>'Produk','sku'=>'SKU','category'=>'Kategori','price'=>'Harga jual','purchase_price'=>'Harga modal','stock'=>'Stok','is_active'=>'Status']],
            'batches'=>['title'=>'Monitoring Stok & Kedaluwarsa','query'=>fn()=>DB::table('product_batches')->join('products','products.id','=','product_batches.product_id')->leftJoin('suppliers','suppliers.id','=','product_batches.supplier_id')->select('products.name as product','product_batches.batch_code','product_batches.quantity','product_batches.received_date','product_batches.expiry_date','suppliers.name as supplier')->where('product_batches.quantity','>',0)->orderBy('product_batches.expiry_date'),'columns'=>['product'=>'Produk','batch_code'=>'Kode batch','quantity'=>'Stok batch','received_date'=>'Tanggal diterima','expiry_date'=>'Kedaluwarsa','supplier'=>'Supplier']],
            'receipts'=>['title'=>'Laporan Penerimaan Barang','query'=>fn()=>DB::table('goods_receipts')->join('suppliers','suppliers.id','=','goods_receipts.supplier_id')->join('users','users.id','=','goods_receipts.user_id')->select('goods_receipts.receipt_number','suppliers.name as supplier','users.name as employee','goods_receipts.received_at','goods_receipts.total')->orderByDesc('goods_receipts.received_at'),'columns'=>['receipt_number'=>'No. penerimaan','supplier'=>'Supplier','employee'=>'Karyawan','received_at'=>'Tanggal','total'=>'Total']],
            'suppliers'=>['title'=>'Laporan Supplier','query'=>fn()=>DB::table('suppliers')->select('name','pic','phone','email','address')->orderBy('name'),'columns'=>['name'=>'Supplier','pic'=>'PIC','phone'=>'Telepon','email'=>'Email','address'=>'Alamat']],
            'orders'=>['title'=>'Monitoring Pesanan','query'=>fn()=>DB::table('orders')->join('users','users.id','=','orders.user_id')->select('orders.order_number','users.name as buyer','orders.total','orders.status','orders.payment_status','orders.created_at')->orderByDesc('orders.created_at'),'columns'=>['order_number'=>'No. pesanan','buyer'=>'Pembeli','total'=>'Total','status'=>'Status pesanan','payment_status'=>'Pembayaran','created_at'=>'Tanggal']],
            'payments'=>['title'=>'Monitoring Pembayaran','query'=>fn()=>DB::table('payments')->join('orders','orders.id','=','payments.order_id')->join('users','users.id','=','orders.user_id')->select('orders.order_number','users.name as buyer','payments.method','payments.amount','payments.status','payments.paid_at')->orderByDesc('payments.created_at'),'columns'=>['order_number'=>'No. pesanan','buyer'=>'Pembeli','method'=>'Metode','amount'=>'Jumlah','status'=>'Status','paid_at'=>'Dibayar pada']],
            'deliveries'=>['title'=>'Monitoring Pengiriman','query'=>fn()=>DB::table('deliveries')->join('orders','orders.id','=','deliveries.order_id')->join('users as buyers','buyers.id','=','orders.user_id')->leftJoin('users as couriers','couriers.id','=','deliveries.courier_id')->select('orders.order_number','buyers.name as buyer','orders.shipping_address','orders.phone','couriers.name as courier','deliveries.tracking_number','deliveries.status','deliveries.updated_at')->orderByDesc('deliveries.updated_at'),'columns'=>['order_number'=>'No. pesanan','buyer'=>'Penerima','shipping_address'=>'Alamat','phone'=>'Telepon','courier'=>'Kurir','tracking_number'=>'Resi','status'=>'Status','updated_at'=>'Pembaruan']],
            'employees'=>['title'=>'Kinerja Karyawan','query'=>fn()=>DB::table('activity_logs')->leftJoin('users','users.id','=','activity_logs.user_id')->select('users.name as employee','activity_logs.action','activity_logs.description','activity_logs.created_at')->where('users.role','employee')->orderByDesc('activity_logs.created_at'),'columns'=>['employee'=>'Karyawan','action'=>'Aktivitas','description'=>'Keterangan','created_at'=>'Waktu']],
        ];
        abort_unless(isset($configs[$type]),404);
        $c=$configs[$type]; $query=($c['query'])();
        if($r->filled('q')) { $q=$r->q; $first=$type==='products' ? 'products.name' : array_key_first($c['columns']); $query->where($first,'like',"%$q%"); }
        $rows=$query->paginate(20)->withQueryString();
        return view('owner.list', ['title'=>$c['title'],'columns'=>$c['columns'],'rows'=>$rows,'type'=>$type]);
    }
}
