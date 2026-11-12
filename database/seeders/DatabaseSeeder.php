<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class DatabaseSeeder extends Seeder {
 public function run(): void {
  $users=[
   ['name'=>'Admin Citra Susu','email'=>'admin@citrasusu.test','role'=>'admin','phone'=>'081234567890'],
   ['name'=>'Siti Karyawan','email'=>'karyawan@citrasusu.test','role'=>'employee','phone'=>'081234567891'],
   ['name'=>'Budi Kurir','email'=>'kurir@citrasusu.test','role'=>'courier','phone'=>'081234567892'],
   ['name'=>'Bunda Suci','email'=>'pembeli@citrasusu.test','role'=>'buyer','phone'=>'081234567893'],
  ];
  foreach($users as $u) User::updateOrCreate(['email'=>$u['email']],$u+['password'=>Hash::make('password'),'is_active'=>true,'chat_enabled'=>true]);
  $cats=['Susu Bayi','Popok Bayi','Perlengkapan Bayi','Perawatan Bayi','Kebutuhan Rumah Tangga'];
  foreach($cats as $name) DB::table('categories')->updateOrInsert(['slug'=>Str::slug($name)],['name'=>$name,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);
  $categoryIds=DB::table('categories')->pluck('id','name');
  $products=[
   ['SGM Ananda 0-6 Bulan 400g','SUS-001','Susu Bayi',85000,'box'],
   ['Dancow Fortigro 800g','SUS-002','Susu Bayi',92000,'box'],
   ['MamyPoko Pants M 20 pcs','POP-001','Popok Bayi',48000,'pack'],
   ['Sweety Bronze M 22 pcs','POP-002','Popok Bayi',43000,'pack'],
   ['Minyak Telon 60ml','PRW-001','Perawatan Bayi',25000,'botol'],
   ['Baby Oil 100ml','PRW-002','Perawatan Bayi',22000,'botol'],
   ['Botol Susu 120ml','BYI-001','Perlengkapan Bayi',35000,'pcs'],
   ['Tisu Basah 50 sheets','BYI-002','Perlengkapan Bayi',18000,'pack'],
  ];
  foreach($products as [$name,$sku,$cat,$price,$unit]){
   $id=DB::table('products')->where('sku',$sku)->value('id');
   if(!$id)$id=DB::table('products')->insertGetId(['category_id'=>$categoryIds[$cat],'sku'=>$sku,'name'=>$name,'description'=>'Produk pilihan Citra Susu untuk kebutuhan keluarga.','price'=>$price,'purchase_price'=>$price*.75,'unit'=>$unit,'stock'=>20,'min_stock'=>5,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);
   if(!DB::table('product_batches')->where('product_id',$id)->exists()){
    $batch=DB::table('product_batches')->insertGetId(['product_id'=>$id,'batch_code'=>'B-'.strtoupper(Str::random(5)),'quantity'=>20,'received_date'=>today(),'expiry_date'=>now()->addMonths(18)->toDateString(),'purchase_price'=>$price*.75,'created_at'=>now(),'updated_at'=>now()]);
    DB::table('stock_movements')->insert(['product_id'=>$id,'batch_id'=>$batch,'user_id'=>1,'type'=>'in','quantity'=>20,'balance'=>20,'reference'=>'SEED','notes'=>'Stok awal demo','created_at'=>now(),'updated_at'=>now()]);
   }
  }
  foreach([['store_name','Citra Susu'],['store_address','Binjai, Sumatera Utara'],['store_phone','081234567890'],['internal_delivery_fee','5000'],['expiry_alert_days','30']] as [$key,$value]) DB::table('store_settings')->updateOrInsert(['key'=>$key],['value'=>$value,'created_at'=>now(),'updated_at'=>now()]);
 }
}
