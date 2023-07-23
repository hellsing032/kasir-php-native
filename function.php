<?php

session_start();

//koneksi ke db
$koneksi = mysqli_connect("localhost","root","","db_kasir");

//Login
if (isset($_POST["login"])) {
    //inisialisasi variabel
    $username = $_POST["username"];
    $password = $_POST["password"];

    $check = mysqli_query($koneksi,"SELECT * FROM user WHERE username='$username' and password='$password'");
    $hitung = mysqli_num_rows($check);

    if ($hitung > 0) {
        //jika datanya berhasil di temukan = berhasil login
        $_SESSION["login"] = "True";
        header("location:index.php");
    } else {
        //data tidak ditemukan = gagal login
        echo '
        <script>alert("Username atau Password salah")
        window.location.href="login.php"
        </script>
        ';
    }
}

if (isset($_POST["tambahbarang"])) {
    $namaproduk = $_POST["namaproduk"];
    $deskripsi = $_POST["deskripsi"];
    $stock = $_POST["stock"];
    $harga = $_POST["harga"];

    $insert = mysqli_query($koneksi,"INSERT INTO produk (namaproduk,deskripsi,harga,stock) values ('$namaproduk','$deskripsi','$harga','$stock')");
    if ($insert) {
        header('location:stock.php');
    } else {
        echo '
        <script>alert("Gagal Menambahkan Barang Baru")
        window.location.href="stock.php"
        </script>
        ';
    }
}

if (isset($_POST["tambahpelanggan"])) {
    $namapelanggan = $_POST["namapelanggan"];
    $notelp = $_POST["notelp"];
    $alamat = $_POST["alamat"];

    $insert = mysqli_query($koneksi,"INSERT INTO pelanggan (namapelanggan,notelp,alamat) values ('$namapelanggan','$notelp','$alamat')");
    if ($insert) {
        header('location:pelanggan.php');
    } else {
        echo '
        <script>alert("Gagal Menambahkan Pelanggan Baru")
        window.location.href="pelanggan.php"
        </script>
        ';
    }
}

if (isset($_POST["tambahpesanan"])) {
    $idpelanggan = $_POST["idpelanggan"];

    $insert = mysqli_query($koneksi,"INSERT INTO pesanan (idpelanggan) values ('$idpelanggan')");
    if ($insert) {
        header('location:index.php');
    } else {
        echo '
        <script>alert("Gagal Menambahkan Pesannan Baru")
        window.location.href="index.php"
        </script>
        ';
    }
}

//produk dipilih untuk di pesan
if (isset($_POST["addproduk"])) {
    $idproduk = $_POST["idproduk"]; //id produk
    $idp = $_POST["idp"]; //id pesanan
    $qty = $_POST["qty"]; // quantity atau jumlah

    //hitung stock barang sekarang ada berapa
    $hitung1 = mysqli_query($koneksi,"select * from produk where idproduk='$idproduk'");
    $hitung2 = mysqli_fetch_array($hitung1);
    $stocksekarang = $hitung2['stock']; // stock barang saat ini

    if($stocksekarang >= $qty){
        //kurangi stock nya dengan jumlah yang akan di keluarkan
        $selisih = $stocksekarang - $qty;
        //stock nya cukup
        $insert = mysqli_query($koneksi,"INSERT INTO detailpesanan (idpesanan,idproduk,qty) values ('$idp','$idproduk','$qty')");
        $update = mysqli_query($koneksi,"UPDATE produk set stock='$selisih' where idproduk='$idproduk'");
        if ($insert&&$update) {
            header('location:view.php?idp='.$idp);
        } else {
            echo '
            <script>alert("Gagal Menambahkan Pesannan Baru")
            window.location.href="view.php?idp='.$idp.'"
            </script>
            ';
        }
        } else {
            //stock nya tidak cukup
            echo '
            <script>alert("Stock Barang Tidak Cukup")
            window.location.href="view.php?idp='.$idp.'"
            </script>
            ';
        }
}

//menambahkan barang masuk
if (isset($_POST['barangmasuk'])){
    $idproduk   = $_POST['idproduk'];
    $qty        = $_POST['qty'];

    //cari tahu stock barang sekarang
    $caristock = mysqli_query($koneksi,"select * from produk where idproduk='$idproduk'");
    $caristock2 = mysqli_fetch_array($caristock);
    $stocksekarang = $caristock2['stock'];

    //hitung stock barang sekarang dengan barang yang masuk
    $newstock = $stocksekarang + $qty;

    $insertb = mysqli_query($koneksi,"INSERT INTO masuk (idproduk,qty) values ('$idproduk','$qty')");
    $updateb = mysqli_query($koneksi,"UPDATE produk set stock='$newstock' where idproduk='$idproduk'");

    if ($insertb&&$updateb){
        header('location:masuk.php');
    }else {
        echo '
        <script>alert("Gagal Menambahkan Barang Masuk")
        window.location.href="masuk.php"
        </script>
        ';
    }
}

//Hapus produk pesanan
if (isset($_POST['hapusprodukpesanan'])) {
    $idp = $_POST['idp']; //id detail pesanan
    $idpr = $_POST['idpr']; //id produk
    $idorder = $_POST['idorder']; //id pesanan

    //Cek qty nya sekarang
    $cek1 = mysqli_query($koneksi,"select * from detailpesanan where iddetailpesanan='$idp'"); //cek qty sekarang
    $cek2 = mysqli_fetch_array($cek1); //fetch array dari cek1
    $qtysekarang = $cek2['qty']; //qty sekarang dari detail pesanan

    //Cek stock barang sekarang
    $cek3 = mysqli_query($koneksi,"select * from produk where idproduk='$idpr'");
    $cek4 = mysqli_fetch_array($cek3);
    $stocksekarang = $cek4['stock'];

    $hitung = $qtysekarang + $stocksekarang;

    $update = mysqli_query($koneksi,"UPDATE produk set stock='$hitung' where idproduk='$idpr'"); //update stock barang
    $delete = mysqli_query($koneksi,"DELETE FROM detailpesanan where idproduk='$idpr' and iddetailpesanan='$idp'"); //hapus detail pesanan

    if($update&&$delete){
        header('location:view.php?idp='.$idorder);
    } else {
        echo '
        <script>alert("Gagal Menghapus Barang")
        window.location.href="view.php?idp='.$idorder.'"
        </script>
        ';
    }
}

//Edit barang
if (isset($_POST['editbarang'])) {
    $np = $_POST['namaproduk'];
    $desc = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $idp = $_POST['idp']; //id produk

    $query = mysqli_query($koneksi,"UPDATE produk set namaproduk='$np', deskripsi='$desc', harga='$harga' where idproduk='$idp'");

    if ($query){
        header('location:stock.php');
    } else {
        echo '
        <script>alert("Gagal Mengedit Barang")
        window.location.href="stock.php"
        </script>
        ';
    }

}

//Hapus barang
if (isset($_POST['hapusbarang'])) {
    $idp = $_POST['idp']; //id produk

    $query = mysqli_query($koneksi,"DELETE FROM produk where idproduk='$idp'");

    if ($query){
        header('location:stock.php');
    } else {
        echo '
        <script>alert("Gagal Menghapus Barang")
        window.location.href="stock.php"
        </script>
        ';
    }

}

//edit pelanggan
if (isset($_POST['editpelanggan'])) {
    $np = $_POST['namapelanggan'];
    $nt = $_POST['notelp'];
    $a = $_POST['alamat'];
    $id = $_POST['idpl']; //id pelanggan

    $query = mysqli_query($koneksi,"UPDATE pelanggan set namapelanggan='$np', notelp='$nt', alamat='$a' where idpelanggan='$id'");

    if ($query){
        header('location:pelanggan.php');
    } else {
        echo '
        <script>alert("Gagal Mengedit Pelanggan")
        window.location.href="pelanggan.php"
        </script>
        ';
    }

}

//hapus pelanggan
if (isset($_POST['hapuspelanggan'])) {
    $idpl = $_POST['idpl']; //id pelanggan

    $query = mysqli_query($koneksi,"DELETE FROM pelanggan where idpelanggan='$idpl'");

    if ($query){
        header('location:pelanggan.php');
    } else {
        echo '
        <script>alert("Gagal Menghapus Pelanggan")
        window.location.href="pelanggan.php"
        </script>
        ';
    }

}

//mengubah data barang masuk
if (isset($_POST['editdatabarangmasuk'])) {
    $idm = $_POST['idm']; //id masuk
    $qty = $_POST['qty']; //qty
    $idp = $_POST['idp']; //id produk

    //cek qty sekarang
    $caritahu = mysqli_query($koneksi,"select * from masuk where idmasuk='$idm'");
    $caritahu2 = mysqli_fetch_array($caritahu);
    $qtysekarang = $caritahu2['qty'];

    //cari tahu stock barang sekarang
    $caristock = mysqli_query($koneksi,"select * from produk where idproduk='$idp'");
    $caristock2 = mysqli_fetch_array($caristock);
    $stocksekarang = $caristock2['stock'];

    if ($qty >= $qtysekarang) {
        //kalau inputan user lebih besar daripada qty yang tercatat
        //hitung selisih nya
        $selisih = $qty - $qtysekarang;
        $newstock =$stocksekarang + $selisih;

        $query1 = mysqli_query($koneksi,"UPDATE masuk set qty='$qty' where idmasuk='$idm'");
        $query2 = mysqli_query($koneksi,"UPDATE produk set stock='$newstock' where idproduk='$idp'");

        if ($query1&&$query2){
            header('location:masuk.php');
        } else {
            echo '
            <script>alert("Gagal Mengubah Data Barang Masuk")
            window.location.href="masuk.php"
            </script>
            ';
        }
    } else {
        //kalau inputan user lebih kecil daripada qty yang tercatat
        //hitung selisih nya
        $selisih = $qtysekarang - $qty;
        $newstock =$stocksekarang - $selisih;

        $query1 = mysqli_query($koneksi,"UPDATE masuk set qty='$qty' where idmasuk='$idm'");
        $query2 = mysqli_query($koneksi,"UPDATE produk set stock='$newstock' where idproduk='$idp'");

        if ($query1&&$query2){
            header('location:masuk.php');
        } else {
            echo '
            <script>alert("Gagal Mengubah Data Barang Masuk")
            window.location.href="masuk.php"
            </script>
            ';
        }
    }
}

// hapus data barang masuk
if (isset($_POST['hapusdatabarangmasuk'])) {
    $idm = $_POST['idm']; //id masuk
    $idp = $_POST['idp']; //id produk

    $caritahu = mysqli_query($koneksi,"select * from masuk where idmasuk='$idm'");
    $caritahu2 = mysqli_fetch_array($caritahu);
    $qtysekarang = $caritahu2['qty'];

    //cari tahu stock barang sekarang
    $caristock = mysqli_query($koneksi,"select * from produk where idproduk='$idp'");
    $caristock2 = mysqli_fetch_array($caristock);
    $stocksekarang = $caristock2['stock'];

    $newstock = $stocksekarang - $qtysekarang;

    $query1 = mysqli_query($koneksi,"DELETE FROM masuk where idmasuk='$idm'");
    $query2 = mysqli_query($koneksi,"UPDATE produk set stock='$newstock' where idproduk='$idp'");

    if ($query1&&$query2){
        header('location:masuk.php');
    } else {
        echo '
        <script>alert("Gagal Menghapus Data Barang Masuk")
        window.location.href="masuk.php"
        </script>
        ';
    }
}

// hapus order
if (isset($_POST['hapusorder'])) {
    $ido = $_POST['ido']; //id pesanan

    $cekdata = mysqli_query($koneksi,"select * from detailpesanan dp where idpesanan='$ido'");

    while($ok = mysqli_fetch_array($cekdata)){
        //balikin stock barang
        $qty = $ok['qty'];
        $idproduk = $ok['idproduk'];
        $iddp = $ok['iddetailpesanan'];

        //cari tahu stock barang sekarang
        $caristock = mysqli_query($koneksi,"select * from produk where idproduk='$idproduk'");
        $caristock2 = mysqli_fetch_array($caristock);
        $stocksekarang = $caristock2['stock'];

        $newstock = $stocksekarang + $qty;

        $queryupdate = mysqli_query($koneksi,"UPDATE produk set stock='$newstock' where idproduk='$idproduk'");

        //hapus data
        $querydelete = mysqli_query($koneksi,"DELETE FROM detailpesanan where iddetailpesanan='$iddp'");
    }

    $query = mysqli_query($koneksi,"DELETE FROM pesanan where idorder='$ido'");
    if ($queryupdate && $querydelete && $query){
        header('location:index.php');
    } else {
        echo '
        <script>alert("Gagal Menghapus Data Order")
        window.location.href="index.php"
        </script>
        ';
    }
}

//Mengubah detail pesanan
if (isset($_POST['editdetailpesanan'])) {
    $iddp = $_POST['iddp']; //id detail pesanan
    $qty = $_POST['qty']; //qty
    $idpr = $_POST['idpr']; //id produk
    $idp = $_POST['idp']; //id pesanan

    //cek atau cari tau qty nya sekarang ada berapa
    $cek1 = mysqli_query($koneksi,"select * from detailpesanan where iddetailpesanan='$iddp'"); //cek qty sekarang
    $cek2 = mysqli_fetch_array($cek1); //fetch array dari cek1
    $qtysekarang = $cek2['qty']; //qty sekarang dari detail pesanan

    //cari tahu stock barang sekarang
    $caristock = mysqli_query($koneksi,"select * from produk where idproduk='$idpr'");
    $caristock2 = mysqli_fetch_array($caristock);
    $stocksekarang = $caristock2['stock'];

    if ($qty >= $qtysekarang) {
        //kalau inputan user lebih besar daripada qty yang tercatat
        //hitung selisih nya
        $selisih = $qty - $qtysekarang;
        $newstock =$stocksekarang - $selisih;

        $query1 = mysqli_query($koneksi,"UPDATE detailpesanan set qty='$qty' where iddetailpesanan='$iddp'");
        $query2 = mysqli_query($koneksi,"UPDATE produk set stock='$newstock' where idproduk='$idpr'");

        if ($query1&&$query2){
            header('location:view.php?idp='.$idp);
        } else {
            echo '
            <script>alert("Gagal Mengubah Data Detail Pesanan")
            window.location.href="view.php?idp='.$idp.'"
            </script>
            ';
        }
    } else {
        // kalau lebih kecil
        //hitung selisih nya
        $selisih    =  $qtysekarang - $qty;
        $newstock   = $stocksekarang + $selisih;

        $query1 = mysqli_query($koneksi,"UPDATE detailpesanan set qty='$qty' where iddetailpesanan='$iddp'");
        $query2 = mysqli_query($koneksi,"UPDATE produk set stock='$newstock' where idproduk='$idpr'");

        if($query1&&$query2){
            header('location:view.php?idp='.$idp);
        } else {
            echo '
            <script>alert("Gagal Mengubah Data Detail Pesanan")
            window.location.href="view.php?idp='.$idp.'"
            </script>
            ';
        }
    }
}

?>