<style>
  .loader {
  border: 3px solid #aeadad;
  border-top: 3px solid #3498db;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  animation: spin 2s linear infinite;
  display: inline-block;
  margin-left: 10px;
  display: none;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
#import-btn {
  margin-top: 5px;
}
.ajax-result {
  padding: 20px;
}
.result-item {
  display: inline-block;
  margin-left: 10px;
  margin-bottom: 5px;
  font-size: 14px;
}
.result-total {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 10px;
}
 /* The Modal (background) */
 .modal {
  display: none; /* Hidden by default */
  position: fixed; /* Stay in place */
  z-index: 1; /* Sit on top */
  left: 0;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: rgb(0,0,0); /* Fallback color */
  background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}
/* Modal Content/Box */
.modal-content {
  background-color: #fefefe;
  margin: 15% auto; /* 15% from the top and centered */
  padding: 15px;
  border: 1px solid #888;
  width: 250px; /* Could be more or less, depending on screen size */
}
/* The Close Button */
.close {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}
.close:hover,
.close:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}
.modal p {
  font-size: 16px;
} 
</style>
<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>" enctype='multipart/form-data'>
        <div id="universal-message-container">
            <div class="options">
                <p>
                    <label>Csv File:</label>
                    <input type='file' name='csv_file' id="csv_file" />
                </p>
        </div>
        <div class="parent">
          <div class="button button-primary" id="import-btn">Import Price Data</div>
          <div class="loader"></div>
        </div>
    </form>
    <div class="ajax-result"></div>
    <div id="alarm-modal" class="modal">
      <div class="modal-content">
        <span class="close">&times;</span>
        <p></p>
      </div>
    </div>
</div>
<?php
    add_action( 'admin_footer', 'ajax_import_pice_data' );

    function ajax_import_pice_data() {
?>
    <script type="text/javascript">
        var modal = document.getElementById("alarm-modal");
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
          modal.style.display = "none";
        }
        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
          if (event.target == modal) {
            modal.style.display = "none";
          }
        } 

        jQuery(document).on('click', '#import-btn', function() {
          if(jQuery("#csv_file").val() == "") {
            jQuery(".modal p").html("please select csv file.");
            modal.style.display = "block";
          }
          else {
            var file = jQuery("#csv_file")[0].files[0];
            var upload = new Upload(file);
            // maby check size or type here with upload.getSize() and upload.getType()
            // execute upload
            upload.doUpload();
          }
        });

        var Upload = function (file) {
            this.file = file;
        };
        Upload.prototype.getType = function() {
            return this.file.type;
        };
        Upload.prototype.getSize = function() {
            return this.file.size;
        };
        Upload.prototype.getName = function() {
            return this.file.name;
        };
        Upload.prototype.doUpload = function () {
            var that = this;
            var formData = new FormData();

            // add assoc key values, this will be posts values
            formData.append("file", this.file, this.getName());
            formData.append("upload_file", true);
            showLoder();
            jQuery(".ajax-result").html("");
            jQuery.ajax({
                type: "POST",
                url: "<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>",
                success: function (data) {
                    // your callback here
                    hideLoder();
                    displayResult(data);
                    jQuery(".modal p").html("success: The price data has been imported.");
                    modal.style.display = "block";
                },
                error: function (error) {
                    // handle error
                    hideLoder();
                    jQuery(".modal p").html("error: An error has occurred in the Ajax communication.");
                    modal.style.display = "block";
                },
                async: true,
                data: formData,
                dataType: "json",
                cache: false,
                contentType: false,
                processData: false,
                timeout: 600000
            });
        };

        function showLoder() {
          jQuery(".loader").css("display", "inline-block");
          jQuery("body").css("cursor", "progress");
        }

        function hideLoder() {
          jQuery(".loader").css("display", "none");
          jQuery("body").css("cursor", "default");
        }

        function displayResult(data) {
          var i = 0;
          var html = '<div class="result-total">Total is '+data.length+' product(s) - The product\'s SKU is:</div>';
          for(i=0; i<data.length; i++) {
            html += '<div class="result-item">'+data[i]+'</div>'
          }
          jQuery(".ajax-result").html(html);
        }
    </script>
<?php } ?>
