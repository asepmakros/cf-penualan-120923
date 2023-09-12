<?php
include('../koneksi/koneksi.php');


?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Harian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
  </head>
  <body>
    <div class="container mt-2">
    <h1>Rekap Penjualan Harian</h1>
    <a href="https://ciwideyfood.com/app/penjualan/data_penjualan.php">back</a>
    <a href="https://ciwideyfood.com/app/penjualan/rekap_stok_harian.php?" class="btn btn-sm btn-primary ">Rekap Stok Terjual Harian</a>
    <a href="https://ciwideyfood.com/app/penjualan/print_packing.php?no_inv=<?= $data['no_inv'] ?>" class="btn btn-sm btn-success ">Print Packing</a>
    <a href="https://ciwideyfood.com/app/penjualan/export/export_id.php" class="btn btn-danger btn-sm">Download Untuk ID</a>


    <!--     
    <form method="post">
        <div class="col-3">
        <label>Tanggal Awal :</label>
        <input name="tanggal_awal" type="date" value="<?php echo date('Y-m-d');?>" class="form-control">
        <label>Tanggal Akhir :</label>
        <input name="tanggal_akhir" type="date" value="<?php echo date('Y-m-d');?>" class="form-control">
        <input name="lihat" type="submit" class="btn btn-primary form-control">
        </div>
    </form> -->
    
    <table class="table table-striped">
        <thead>
            <th>#</th>
            <th>Orderan</th>
            <!--<th>Jumlah</th>-->
            
        </thead>
        <tbody>
            <?php 
            // if(isset($_POST['lihat'])){
            $tanggal_awal = $_POST['tanggal_awal'];
            $tanggal_akhir = $_POST['tanggal_akhir'];
            $no = 1;
            $sql = $koneksi->query("select *, sum(jumlah*satuan) as jum from sales where approve ='Y' and gudang = 'y'  group by no_inv order by pelanggan asc");
            while ($data = $sql->fetch_assoc()) {
            ?>
            <tr 
            <?php 
            if($data['produks']!= ''){
                echo "class='bg-success'";
            }
            ?>
            >
                <td><?php echo $no++." of ".count($data) ?></td>
                <td><?php echo $data['pelanggan']."=>".$data['alamat'] ?><br>
                
                <?php
                $pelang = $data['pelanggan'] ;
                $no_inv = $data['no_inv'] ;
                $pembayaran = explode(" ",$data['pelanggan'])[0];
                if($pembayaran != "COD"){
                    $pembayaran = "NON-COD";
                }
                $kurir = explode("- ",$data['pelanggan'])[1];
                if($kurir == "ID"){
                    $kurir = "id express_standard";
                }
                $prod = [];
                $jumlah = [];
                $massa = [];
                $nilai = [];

                $sqlbrg = $koneksi->query("select *,sum(sales.jumlah * sales.satuan) as total from sales, tb_barang where no_inv = '$no_inv' and produk != ' Ongkir' and tb_barang.nama_barang = sales.produk");
                while ($data = $sqlbrg->fetch_assoc()) {
                echo " ".$data['produk']." (".$data['jumlah']."), "  ;

                array_push($prod,$data['produk']." (".$data['jumlah'].")");
                array_push($jumlah,$data['jumlah']);
                array_push($massa,$data['berat']*$data['jumlah']);
                array_push($nilai,$data['total']);


            }
            $jumlah_item = array_sum($jumlah);
            $berat = array_sum($massa);
            $nilai_barang = array_sum($nilai);

                ?>
                                                                                      
                <form action="" method="post" class="d-print-none">
                    <select name="tujuan" required id="" class="d-print-none">
                        <?php
                        $sqltujuan = $koneksi->query("select * from tb_ro_cities,tb_ro_subdistricts,tb_ro_provinces where tb_ro_cities.province_id = tb_ro_provinces.province_id and tb_ro_cities.city_id = tb_ro_subdistricts.city_id  order by tb_ro_cities.city_id asc ");
                        ?>
                        <option value="" selected disabled>Pilih alamat tujuan</option>
                        <?php while($kota = $sqltujuan->fetch_assoc()){?>
                        <option value="<?= $kota['subdistrict_name'].", ".$kota['city_name'].", ".$kota['province_name'].", ".$kota['postal_code']?>"><?= $kota['subdistrict_name'].", ".$kota['city_name'].", ".$kota['province_name'].", ".$kota['postal_code']?></option>
                        <?php } ?>
                    </select>
                    <input type="text" class="form-control" hidden name="no_inv" value="<?= $no_inv ?>">

                    <input type="text" class="form-control" hidden name="jumlah_item" value="<?= $jumlah_item ?>">
                    <input type="text" class="form-control" hidden name="produks" value="<?= implode(", ",$prod); ?>">
                    <input type="text" class="form-control" hidden name="berat" value="<?= $berat ?>">
                    <input type="text" class="form-control" hidden name="nilai_barang" value="<?= $nilai_barang ?>">
                    <input type="text" class="form-control" hidden name="pembayaran" value="<?= $pembayaran ?>">
                    <input type="text" class="form-control" hidden name="kurir" value="<?= $kurir ?>">



                    <input type="submit" class="" name="upload" value="upload" class="btn btn-sm btn-success">
                </form>

                <?php
                if(isset($_POST['upload'])){
                    $no_inv = $_POST['no_inv'];
                    $kabupaten = explode(",",$_POST['tujuan'])[1];
                    $kecamatan = explode(",",$_POST['tujuan'])[0];
                    $kode_pos = explode(",",$_POST['tujuan'])[3];
                    $produks = $_POST['produks'];
                    $jumlah_item = $_POST['jumlah_item'];
                    $berat = $_POST['berat'];
                    $pembayaran = $_POST['pembayaran'];
                    $kurir = $_POST['kurir'];
                    $nilai_barang = $_POST['nilai_barang'];


                    $sql_tujuan = $koneksi->query("
                    update sales set
                    kabupaten = '$kabupaten',
                    kecamatan = '$kecamatan',
                    kode_pos = '$kode_pos',
                    produks = '$produks',
                    jumlah_item = '$jumlah_item',
                    berat = '$berat',
                    pembayaran = '$pembayaran',
                    kurir = '$kurir',
                    nilai_barang = '$nilai_barang'
                  
                   
                
                    where no_inv = '$no_inv'
                    ");
                
                    if($sql_tujuan){
                        ?> 
                        
                        <script>
                            alert("Data berhasil diubah!");
                            window.location.href = "https://ciwideyfood.com/app/penjualan/rekap_jual_harian.php";
                
                        </script>
        
                 <?php 
            
                 }
                ?>   
                </td>
            </tr>
           
       <?php 
       }
        
        }
         ?>
        </tbody>
    </table>


    <!-- pake row
    <div class="row">

    <?php
      $no = 1;
      $sql = $koneksi->query("select *, sum(jumlah*satuan) as jum from sales where approve ='Y' and gudang = 'y'  group by pelanggan order by id desc");
     while ($data = $sql->fetch_assoc()) {
    ?>
        <div class="col-md-auto">
            <div class="card" style="width: 18rem;">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $no++.". ". $data['pelanggan'] ?></h5>
                <h6 class="card-subtitle mb-2 text-muted"><?php echo $data['j_pembeli'] ?></h6>
                <p class="card-text">
                    <?php
                        $pelang = $data['pelanggan'] ;
                        $sqlbrg = $koneksi->query("select * from sales where pelanggan = '$pelang' and produk != ' Ongkir'");
                        while ($data = $sqlbrg->fetch_assoc()) {
                        echo " ".$data['produk']." (".$data['jumlah'].") <br>
                        "  ;
                        }
                    ?>
                    </p>
                 </div>
            </div>
        </div>
        <?php } ?>

    </div> -->


    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
  </body>
</html>