<?php
include 'koneksi.php';

// Pastikan ID dan ACTION ada di URL
if(isset($_GET['id']) && isset($_GET['action'])){
    $id = mysqli_real_escape_string($conn, $_GET['id']); // Keamanan tambahan
    $action = $_GET['action'];
    $waktu_sekarang = date('Y-m-d H:i:s');
    
    if($action == 'resolve'){
        // Logika untuk menandai selesai (seperti kode lama kamu)
        $query = mysqli_query($conn, "UPDATE logs SET 
                                      status = 'Resolved', 
                                      ditangani_pada = '$waktu_sekarang' 
                                      WHERE id = '$id'");
        
        if($query){
            header("Location: index.php?status=resolved");
        } else {
            echo "Gagal mengupdate status.";
        }

    } else if($action == 'delete'){
        // Logika baru untuk menghapus data
        $query = mysqli_query($conn, "DELETE FROM logs WHERE id = '$id'");
        
        if($query){
            header("Location: index.php?status=deleted");
        } else {
            echo "Gagal menghapus data.";
        }
    }
} else {
    // Jika diakses tanpa parameter yang benar, lempar balik ke index
    header("Location: index.php");
}
?>