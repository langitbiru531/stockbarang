<?php
session_start();

//Membuat Koneksi ke DataBase
$conn = mysqli_connect("localhost","root","","stockbarang");


//Menanbah Barang Baru
if(isset($_POST['addnewbarang'])){
	$namabarang = $_POST['namabarang'];
	$deskripsi = $_POST['deskripsi'];
	$stock = $_POST['stock'];

	//soal gambar
	$allowed_extension = array('png', 'jpg');
	$nama = $_FILES['file']['name']; //ngambil nama gambar
	$dot = explode('.', $nama);
	$ekstensi = strtolower(end($dot)); //ngambil ekstensinya
	$ukuran = $_FILES['file']['size']; //ngambil size filenya
	$file_tmp = $_FILES['file']['tmp_name']; //ngambil lokasi filenya


	//penamaan file -> enkripsi
	$image = md5(uniqid($nama,true) . time()).'.'.$ekstensi; //menggabungkan nama file yg dienkripsi dg ekstensinya

	//validasi uda ada atau belom
	$cek = mysqli_query($conn,"select * from stock where namabarang='$namabarang'");
	$hitung =mysqli_num_rows($cek);

	if($hitung<1){
		//Jika belum ada

		//proses upload gambar
		if(in_array($ekstensi, $allowed_extension) === true){

			//validasi ukuran filenya
			if($ukuran < 15000000){
				move_uploaded_file($file_tmp, 'images/'.$image);

				$addtotable = mysqli_query($conn,"insert into stock (namabarang, deskripsi, stock, image) values('$namabarang','$deskripsi','$stock','$image')");
			if($addtotable){
				header('location:index.php');
			} else {
				echo 'Gagal';
				header('location:index.php');
			}
		} else {
				//kalau filenya lebih dari 15mb
				echo '
				<script>
					alert("Ukuran file terlalu besar");
					window.location.href="index.php";
				</script>
				'; 
			}
		} else {
			//kalau filenya tidak png/jpg
			echo '
			<script>
				alert("File harus png/jpg");
				window.location.href="index.php";
			</script>
			';

		}

	} else {
		//jika sudah ada
		echo '
		<script>
			alert("Nama barang sudah terdaftar");
			window.location.href="index.php";
		</script>
		'; 

	}
}


//Menambah barang masuk
if(isset($_POST['barangmasuk'])){
	$barangnya = $_POST['barangnya'];
	$penerima = $_POST['penerima'];
	$qty = $_POST['qty'];

	$cekstocksekarang = mysqli_query($conn,"select * from stock where idbarang='$barangnya'");
	$ambildatanya = mysqli_fetch_array($cekstocksekarang);

	$stocksekarang = $ambildatanya['stock'];
	$tambahkanstocksekarangdenganquantity = $stocksekarang+$qty;

	$addtomasuk = mysqli_query($conn,"insert into masuk (idbarang, keterangan, qty) values('$barangnya','$penerima','$qty')");
	$updatestockmasuk = mysqli_query($conn,"update stock set stock='$tambahkanstocksekarangdenganquantity' where idbarang='$barangnya'");
	if($addtomasuk&&$updatestockmasuk){
		header('location:masuk.php');
	} else {
		echo 'Gagal';
		header('location:masuk.php');
	}
}

//Menambah barang keluar
if(isset($_POST['addbarangkeluar'])){
	$barangnya = $_POST['barangnya'];
	$penerima = $_POST['penerima'];
	$qty = $_POST['qty'];

	$cekstocksekarang = mysqli_query($conn,"select * from stock where idbarang='$barangnya'");
	$ambildatanya = mysqli_fetch_array($cekstocksekarang);

	$stocksekarang = $ambildatanya['stock'];

	if($stocksekarang >= $qty){
		//Kalau barangnya cukup
		$tambahkanstocksekarangdenganquantity = $stocksekarang-$qty;

		$addtokeluar = mysqli_query($conn,"insert into keluar (idbarang, penerima, qty) values('$barangnya','$penerima','$qty')");
		$updatestockmasuk = mysqli_query($conn,"update stock set stock='$tambahkanstocksekarangdenganquantity' where idbarang='$barangnya'");
		if($addtokeluar&&$updatestockmasuk){
			header('location:keluar.php');
		} else {
			echo 'Gagal';
			header('location:keluar.php');
		}
	} else {
		//kalau barangnya nggak cukup
		echo '
		<script>
			alert("stock saat ini tidak mencukupi");
			window.location.href="keluar.php";
		</script>
		';
	}
}



//Update info barang
if(isset($_POST['updatebarang'])){
	$idb = $_POST['idb'];
	$namabarang = $_POST['namabarang'];
	$deskripsi = $_POST['deskripsi'];
	$stock = $_POST['stock'];

	//soal gambar
	$allowed_extension = array('png', 'jpg');
	$nama = $_FILES['file']['name']; //ngambil nama gambar
	$dot = explode('.', $nama);
	$ekstensi = strtolower(end($dot)); //ngambil ekstensinya
	$ukuran = $_FILES['file']['size']; //ngambil size filenya
	$file_tmp = $_FILES['file']['tmp_name']; //ngambil lokasi filenya


	//penamaan file -> enkripsi
	$image = md5(uniqid($nama,true) . time()).'.'.$ekstensi; //menggabungkan nama file yg dienkripsi dg ekstensinya

	if ($ukuran==0){
		//Jika tidak ingin upload
		$update = mysqli_query($conn,"update stock set namabarang='$namabarang', deskripsi='$deskripsi', stock='$stock' where idbarang='$idb'");
		if($update){
			header('location:index.php');
		} else {
			echo 'Gagal';
			header('location:index.php');
		} 
	} else {
		//Jika ingin
		move_uploaded_file($file_tmp, 'images/'.$image);
		$update = mysqli_query($conn,"update stock set namabarang='$namabarang', deskripsi='$deskripsi', stock='$stock', image='$image' where idbarang='$idb'");
		if($update){
			header('location:index.php');
		} else {
			echo 'Gagal';
			header('location:index.php');
		}
	}
}


//Menghapus barang dari stock
if(isset($_POST['hapusbarang'])){
	$idb = $_POST['idb'];

	$gambar = mysqli_query($conn,"select * from stock where idbarang='$idb'");
	$get = mysqli_fetch_array($gambar);
	$img = 'images/'.$get['image'];
	unlink($img);

	$update = mysqli_query($conn,"delete from stock where idbarang='$idb'");
	if($hapus){
		header('location:index.php');
	} else {
		echo 'Gagal';
		header('location:index.php');
	}
}



//Mengubah data barang masuk
if(isset($_POST['updatebarangmasuk'])){
	$idb = $_POST['idb'];
	$idm = $_POST['idm'];
	$deskripsi = $_POST['keterangan'];
	$qty = $_POST['qty'];

	$lihatstock = mysqli_query($conn,"select * from stock where idbarang='$idb'");
	$stocknya = mysqli_fetch_array($lihatstock);
	$stockskrng = $stocknya['stock'];

	$qtyskrng = mysqli_query($conn,"select * from masuk where idmasuk='$idm'");
	$qtynya = mysqli_fetch_array($qtyskrng);
	$qtyskrng = $qtynya['qty'];

	if($qty>$qtyskrng){
		$selisih = $qty-$qtyskrng;
		$kurangin = $stockskrng + $selisih;
		$kurangistocknya = mysqli_query($conn,"update stock set stock='$kurangin' where idbarang='$idb'");
		$updatenya = mysqli_query($conn,"update masuk set qty='$qty', keterangan='$deskripsi' where idmasuk='$idm'");
			if($kurangistocknya&&$updatenya){
				header('location:masuk.php');
				} else {
					echo 'Gagal';
					header('location:masuk.php');
			}
	} else {
		$selisih = $qtyskrng-$qty;
		$kurangin = $stockskrng - $selisih;
		$kurangistocknya = mysqli_query($conn,"update stock set stock ='$kurangin' where idbarang='$idb'");
		$updatenya = mysqli_query($conn,"update masuk set qty='$qty', keterangan='$deskripsi' where idmasuk='$idm'");
			if($kurangistocknya&&$updatenya){
				header('location:masuk.php');
				} else {
					echo 'Gagal';
					header('location:masuk.php');
			}
	}
}



//Menghapus barang masuk
if(isset($_POST['hapusbarangmasuk'])){
	$idb = $_POST['idb'];
	$qty = $_POST['kty'];
	$idm = $_POST['idm'];
	
	$getdatastock = mysqli_query($conn,"select * from stock where idbarang='$idb'");
	$data = mysqli_fetch_array($getdatastock);
	$stock = $data['stock'];

	$selisih = $stock-$qty;

	$update = mysqli_query($conn,"update stock set stock='$selisih' where idbarang='$idb'");
	$hapusdata = mysqli_query($conn,"delete from masuk where idmasuk='$idm'");

	if($update&&$hapusdata){
		header('location:masuk.php');
	} else {
		header('location:masuk.php');
	}

}



//Mengubah data barang keluar
if(isset($_POST['updatebarangkeluar'])){
	$idb = $_POST['idb'];
	$idk = $_POST['idk'];
	$penerima = $_POST['penerima'];
	$qty = $_POST['qty']; //Qty baru inputan user

	//Mengambil stock barang saat ini
	$lihatstock = mysqli_query($conn,"select * from stock where idbarang='$idb'");
	$stocknya = mysqli_fetch_array($lihatstock);
	$stockskrng = $stocknya['stock'];

	//Qty barang keluar saat ini
	$qtyskrng = mysqli_query($conn,"select * from keluar where idkeluar='$idk'");
	$qtynya = mysqli_fetch_array($qtyskrng);
	$qtyskrng = $qtynya['qty'];

	if($qty>$qtyskrng){
		$selisih = $qty-$qtyskrng;
		$kurangin = $stockskrng - $selisih;

		if($selisih <= $stockskrng){
			$kurangistocknya = mysqli_query($conn,"update stock set stock='$kurangin' where idbarang='$idb'");
			$updatenya = mysqli_query($conn,"update keluar set qty='$qty', penerima='$penerima' where idkeluar='$idk'");
				if($kurangistocknya&&$updatenya){
					header('location:keluar.php');
					} else {
						echo 'Gagal';
						header('location:keluar.php');
				}
			} else {
				echo'
				<script>alert("Stock tidak mencukupi");
				window.location.href="keluar.php";
				</script>
				';
			}	
		
		
	} else {
		$selisih = $qtyskrng-$qty;
		$kurangin = $stockskrng + $selisih;
		$kurangistocknya = mysqli_query($conn,"update stock set stock='$kurangin' where idbarang='$idb'");
		$updatenya = mysqli_query($conn,"update keluar set qty='$qty', penerima='$penerima' where idkeluar='$idk'");
			if($kurangistocknya&&$updatenya){
				header('location:keluar.php');
				} else {
					echo 'Gagal';
					header('location:keluar.php');
			}
	}
}




//Menghapus barang keluar
if(isset($_POST['hapusbarangkeluar'])){
	$idb = $_POST['idb'];
	$qty = $_POST['kty'];
	$idk = $_POST['idk'];
	
	$getdatastock = mysqli_query($conn,"select * from stock where idbarang='$idb'");
	$data = mysqli_fetch_array($getdatastock);
	$stock = $data['stock'];

	$selisih = $stock+$qty;

	$update = mysqli_query($conn,"update stock set stock='$selisih' where idbarang='$idb'");
	$hapusdata = mysqli_query($conn,"delete from keluar where idkeluar='$idk'");

	if($update&&$hapusdata){
		header('location:keluar.php');
	} else {
		header('location:keluar.php');
	}

}




//menambah admin baru
if(isset($_POST['addadmin'])){
	$email = $_POST['email'];
	$password = $_POST['password'];

	$queryinsert = mysqli_query($conn,"insert into login (email, password) values ('$email','$password')");

	if ($queryinsert) {
		//if berhasil
		header('location:admin.php');

	} else {
		//kalau gagal insert ke db
		header('location:admin.php');
	}
}




//edit data admin
if(isset($_POST['updateadmin'])){
	$emailbaru = $_POST['emailadmin'];
	$passwordbaru = $_POST['passwordbaru'];
	$idnya = $_POST['id'];

	$queryupdate = mysqli_query($conn,"update login set email='$emailbaru', password='$passwordbaru' where iduser='$idnya'");

	if ($queryupdate) {
		header('location:admin.php');

	} else {
		header('location:admin.php');
	}
}




//hapus admin
if(isset($_POST['hapusadmin'])){
	$id = $_POST['id'];

	$querydelete = mysqli_query($conn,"delete from login where iduser='$id'");

	if ($querydelete) {
		header('location:admin.php');

	} else {
		header('location:admin.php');
	}
}


//meminjam barang
if(isset($_POST['pinjam'])){
	$idbarang = $_POST['barangnya']; //mengambil id barang
	$qty = $_POST['qty']; //mengambil jumlah quantity
	$penerima = $_POST['penerima']; //mengambil nama penerima

	//ambil stock sekarang
	$stok_saat_ini = mysqli_query($conn,"select * from stock where idbarang='$idbarang'");
	$stok_nya = mysqli_fetch_array($stok_saat_ini);
	$stok = $stok_nya['stock'];//ini value nya

	//kurangin stocknya
	$new_stock = $stok-$qty;

	//mulai query insert
	$insertpinjam = mysqli_query($conn,"INSERT INTO peminjaman (idbarang,qty,peminjam) values ('$idbarang','$qty','$penerima')");

	//Mengurangi stock di table stock
	$kurangistock = mysqli_query($conn,"update stock set stock='$new_stock' where idbarang='$idbarang'");

	if($insertpinjam&&$kurangistock){
		//jika berhasil
		echo '
		<script>
			alert("Berhasil");
			window.location.href="peminjaman.php";
		</script>
		';
	} else {
		//jika gagal
		echo '
		<script>
			alert("Gagal");
			window.location.href="peminjaman.php";
		</script>
		';
	}
}

//menyelesaikan pinjaman
if(isset(($_POST['barangkembali']))){
	$idpinjam = $_POST['idpinjam'];
	$idbarang = $_POST['idbarang'];

	//eksekusi
	$update_status = mysqli_query($conn,"update peminjaman set status='Kembali' where idpeminjaman='$idpinjam'");

	//ambil stock sekarang
	$stok_saat_ini = mysqli_query($conn,"select * from stock where idbarang='$idbarang'");
	$stok_nya = mysqli_fetch_array($stok_saat_ini);
	$stok = $stok_nya['stock'];//ini value nya

	//ambil qty si idpinjam sekarang
	$stok_saat_ini1 = mysqli_query($conn,"select * from peminjaman where idpeminjaman='$idpinjam'");
	$stok_nya1 = mysqli_fetch_array($stok_saat_ini1);
	$stok1 = $stok_nya1['qty'];//ini value nya

	//kurangin stocknya
	$new_stock = $stok1+$stok;

	//kembalikan stocknya
	$kembalikan_stock = mysqli_query($conn,"update stock set stock='$new_stock' where idbarang='$idbarang'");

	if($update_status&&$kembalikan_stock){
		//jika berhasil
		echo '
		<script>
			alert("Berhasil");
			window.location.href="peminjaman.php";
		</script>
		';
	} else {
		//jika gagal
		echo '
		<script>
			alert("Gagal");
			window.location.href="peminjaman.php";
		</script>
		';
	}
}




?>