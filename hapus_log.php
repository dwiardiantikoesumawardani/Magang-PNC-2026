<?php
include 'koneksi.php';

if(isset($_GET['id'])){
    $id = $_GET['id'];
    
    // Query untuk menghapus data berdasarkan ID
    $query = mysqli_query($conn, "DELETE FROM logs WHERE id='$id'");
    
    if($query){
        // Jika berhasil, balik lagi ke dashboard
        header("Location: index.php?status=deleted");
    } else {
        echo "Gagal menghapus data: " . mysqli_error($conn);
    }
} else {
    header("Location: index.php");
}
?>