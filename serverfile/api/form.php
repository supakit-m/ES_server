<?php
$url = "https://www.mesb.in.th"; // URL ที่ถูกต้อง
?>

<form id="emailForm" action='/web/api/sendEmail.php' method='post'>
   Name: <input type='text' name='name' id='name' required><br>
   Email: <input type='email' name='email' required><br>
   <input type='hidden' name='subject' value="Verify Your Email Address - MESB Admin">
   <input type='hidden' name='body' id='body' value="">
   <input type='hidden' name='altBody' id='altBody' value="">
   <input type='submit' value='   ส่ง   '>
</form>

<script>
document.getElementById('emailForm').onsubmit = function() {
    var name = document.getElementById('name').value;
    var url = "<?php echo $url; ?>"; // รับค่า URL จาก PHP

    // สร้างเนื้อหาอีเมล
    var body = `
      <html>
      <head>
        <title>Verify Your Email Address - MESB Admin</title>
      </head>
      <body>
        <p>Dear ${name},</p>
        <p>Thank you for registering with MESB Admin. Please click the link below to verify your email address:</p>
        <p><a href='${url}'>Verify Email</a></p>
        <p>If you did not create an account with MESB Admin, please ignore this email.</p>
        <br>
        <p>Best regards,</p>
        <p>The MESB Admin Team</p>
      </body>
      </html>
    `;
    
    var altBody = `Dear ${name},\n\nThank you for registering with MESB Admin. Please visit the following link to verify your email address: ${url}\n\nIf you did not create an account with MESB Admin, please ignore this email.\n\nBest regards,\nThe MESB Admin Team`;

    // ใส่ค่า body และ altBody ใน hidden input
    document.getElementById('body').value = body;
    document.getElementById('altBody').value = altBody;
};
</script>

