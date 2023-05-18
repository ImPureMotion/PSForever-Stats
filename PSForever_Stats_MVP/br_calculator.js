// Function to calculate the BR number based on the BEP number
function calculateBR(bep) {
    const thresholds = [
      { "br": 1, "bepThreshold": 999 },
      { "br": 2, "bepThreshold": 2999 },
      { "br": 3, "bepThreshold": 7499 },
      { "br": 4, "bepThreshold": 14999 },
      { "br": 5, "bepThreshold": 29999 },
      { "br": 6, "bepThreshold": 44999 },
      { "br": 7, "bepThreshold": 67499 },
      { "br": 8, "bepThreshold": 101249 },
      { "br": 9, "bepThreshold": 126562 },
      { "br": 10, "bepThreshold": 158202 },
      { "br": 11, "bepThreshold": 197753 },
      { "br": 12, "bepThreshold": 247191 },
      { "br": 13, "bepThreshold": 308989 },
      { "br": 14, "bepThreshold": 386238 },
      { "br": 15, "bepThreshold": 482797 },
      { "br": 16, "bepThreshold": 603496 },
      { "br": 17, "bepThreshold": 754370 },
      { "br": 18, "bepThreshold": 942963 },
      { "br": 19, "bepThreshold": 1178704 },
      { "br": 20, "bepThreshold": 1438019 },
      { "br": 21, "bepThreshold": 1710300 },
      { "br": 22, "bepThreshold": 1988027 },
      { "br": 23, "bepThreshold": 2286230 },
      { "br": 24, "bepThreshold": 2583440 },
      { "br": 25, "bepThreshold": 2908441 },
      { "br": 26, "bepThreshold": 3237941 },
      { "br": 27, "bepThreshold": 3618441 },
      { "br": 28, "bepThreshold": 3988841 },
      { "br": 29, "bepThreshold": 4488541 },
      { "br": 30, "bepThreshold": 5027341 },
      { "br": 31, "bepThreshold": 5789641 },
      { "br": 32, "bepThreshold": 6861341 },
      { "br": 33, "bepThreshold": 8229241 },
      { "br": 34, "bepThreshold": 10000541 },
      { "br": 35, "bepThreshold": 11501741 },
      { "br": 36, "bepThreshold": 13184641 },
      { "br": 37, "bepThreshold": 15049041 },
      { "br": 38, "bepThreshold": 17094941 },
      { "br": 39, "bepThreshold": 19216141 },
      { "br": 40, "bepThreshold": 21416641 },
      // Add more BR thresholds as needed
    ];
  
    for (let i = thresholds.length - 1; i >= 0; i--) {
        if (bep >= thresholds[i].bepThreshold) {
          return thresholds[i].br;
        }
      }
    
      return 0; // Default BR if no threshold matches
    }
    
    // Function to create a table row for a player
    function createTableRow(player) {
      const table = document.getElementById("myTable");
      const row = table.insertRow(-1);
    
      const nameCell = row.insertCell(0);
      const brCell = row.insertCell(1);
    
      nameCell.textContent = player.name;
      brCell.textContent = calculateBR(player.bep);
    }
    
    // Function to populate the leaderboard table with player data
    function populateLeaderboard() {
      const table = document.getElementById("myTable");
    
      // Remove existing rows from the table
      while (table.rows.length > 0) {
        table.deleteRow(0);
      }
    
      // Add table headers
      const headerRow = table.insertRow(0);
      headerRow.insertCell(0).textContent = "Player Name";
      headerRow.insertCell(1).textContent = "BR";
    
      // Add player rows
      for (let i = 0; i < 10; i++) {
        createTableRow(playerData[i]);
      }
    }
    
    // Call the function to populate the leaderboard table
    populateLeaderboard();