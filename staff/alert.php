<script src="assets/js/sweetalert.min.js"></script>
<?php
if (isset($_SESSION['status'])) {
    ?>
<script>
swal({
    title: "<?php echo $_SESSION['status']; ?>",
    text: "<?php echo $_SESSION['status_text']; ?>",
    icon: "<?php echo $_SESSION['status_code']; ?>",
    button: "<?php echo $_SESSION['status_btn']; ?>",
});
</script>

<?php
unset($_SESSION['status']);
}
?>