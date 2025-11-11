<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Barangay System Portal</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      height: 100vh;
      background: linear-gradient(135deg, #2575fc, #1111),
                  url('images/logo.png') repeat center center fixed;
      background-size: 800px;
      background-blend-mode: overlay;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #fff;
    }

    .container {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      padding: 40px;
      border-radius: 15px;
      text-align: center;
      width: 400px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    h1 {
      font-size: 28px;
      margin-bottom: 15px;
      font-weight: 600;
    }

    p {
      margin-bottom: 25px;
      font-size: 16px;
      opacity: 0.9;
    }

    select {
      width: 100%;
      padding: 12px;
      font-size: 16px;
      border-radius: 8px;
      border: none;
      background: rgba(255, 255, 255, 0.9);
      color: #333;
      cursor: pointer;
      margin-bottom: 20px;
    }

    select:focus {
      outline: none;
      box-shadow: 0 0 5px #2575fc;
    }

    button {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background-color: #fff;
      color: #2575fc;
      font-size: 18px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background-color: #2575fc;
      color: #fff;
      transform: scale(1.03);
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Barangay Portal</h1>
    <p>Select your role to continue:</p>

    <form id="roleForm">
      <select id="roleSelect" required>
        <option value="">-- Choose a Role --</option>
        <option value="admin_login.php">👨‍💼 Admin</option>
        <option value="login.php">👤 User</option>
        <option value="rider_login.php">🚴 Rider</option>

      </select>

      <button type="submit">Continue</button>
    </form>
  </div>

  <script>
    document.getElementById('roleForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const selectedPage = document.getElementById('roleSelect').value;
      if (selectedPage) {
        window.location.href = selectedPage;
      } else {
        alert('Please select a role first!');
      }
    });
  </script>
</body>
</html>
