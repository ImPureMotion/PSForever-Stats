/*
const players = [];

async function getPlayerData() {
    const response = await fetch('https://play.psforever.net/api/char_stats_cep/0')
    if (response.status === 200) {
      const players = await response.json()
      console.log(players)
    } else {
      console.log('Error:', response.statusText)
    }
  }
  
getPlayerData()
*/
const proxyurl = "https://corsproxy.io/?";
const targetUrl = 'https://play.psforever.net/api/char_stats_cep/0';
const players = [];
const len = 50;

async function display_query(event) {
  const match = document.getElementById("table-search").value.trim().toLowerCase();
  const tableRows = document.querySelectorAll("#myTable tbody tr");

  tableRows.forEach(row => {
    const playerName = row.id.toLowerCase();
    if (match === "" || playerName.includes(match)) {
      row.style.display = "table-row";
    } else {
      row.style.display = "none";
    }
  });
}

async function clear_query(event) {
  for (var i = 0; i < len; i++) {
    try {
      document.getElementById("table-search").value = "";
      document.getElementById(`${players[i].name}`).style.display = "table-row";
    } catch {
      continue;
    }
  }
}

async function getPlayerData() {
  try {
    const response = await fetch(proxyurl + encodeURIComponent(targetUrl));

    if (!response.ok) {
      console.log(`An error occurred: ${response.statusText}`);
      return null;
    }

    const data = await response.json();
    const topPlayers = data.players.slice(0, 50); // Limit to top 50 players
    return topPlayers;
  } catch (error) {
    console.log("An error occurred while fetching the data:", error);
    return null;
  }
}


async function main() {
  const topPlayers = await getPlayerData();
  console.log(topPlayers);

  // Now topPlayers is an array of the top 50 player objects
  // Store it in the players array
  players.push(...topPlayers);


  // Calculate and assign kills property to each player
  players.forEach(player => {
    player.kills = parseInt(player.cep) / 100;
  });

  const tableBody = document.querySelector('#myTable tbody');
  
  // Sort players array by kills in descending order
  players.sort((a, b) => b.kills - a.kills);
  
  players.slice(0, 50).forEach((player, index) => {
    const rank = index + 1;
  
    const row = document.createElement('tr');
  
    const kills = player.kills;
    const cep = player.cep;
  
    let factionImageSrc;
    let rowColor;
  
    if (player.faction_id === 0) {
      factionImageSrc = "Images/Empires-tr-icon.webp"; // Replace with the image source for TR
      rowColor = "rgba(255, 0, 0, 0.2)"; // Transparent light red
    } else if (player.faction_id === 1) {
      factionImageSrc = "Images/Empires-nc-icon.webp"; // Replace with the image source for NC
      rowColor = "rgba(0, 0, 255, 0.2)"; // Transparent light blue
    } else if (player.faction_id === 2) {
      factionImageSrc = "Images/Empires-vs-icon.webp"; // Replace with the image source for VS
      rowColor = "rgba(128, 0, 128, 0.2)"; // Transparent light purple
    } else {
      factionImageSrc = "path/to/unknown_image.jpg"; // Replace with the image source for unknown faction
      rowColor = "transparent";
    }
  
    if (rank === 1) {
      rowColor = "rgba(255, 215, 0, 0.2)"; // Transparent gold for the number one player
    }
  
    const rankCell = document.createElement("td");
    const factionCell = document.createElement("td");
    const factionImage = document.createElement("img"); // Create an img element
  
    rankCell.textContent = rank;
    factionImage.src = factionImageSrc; // Set the src attribute of the img element to the faction image source
    factionImage.style.width = "50%"; // Set the width of the img element to 50% of its original size
  
    factionCell.appendChild(factionImage); // Append the img element to the faction cell
  
    row.id = player.name;
    row.style.backgroundColor = rowColor;
    row.appendChild(rankCell);
    row.appendChild(factionCell);
    row.innerHTML += `
      <td>${player.name}</td>
      <td>${kills}</td>
      <td>${calculateBR(player.bep)}</td>
      <td>${calculateCR(player.cep)}</td>
      <td>${player.bep}</td>
      <td>${cep}</td>
    `;
  
    tableBody.appendChild(row);
  });
  function calculateTotalKillCountByFaction(players) {
    const factionKillCounts = {
      0: 0, // Initialize kill count for Faction ID 0 (TR)
      1: 0, // Initialize kill count for Faction ID 1 (NC)
      2: 0  // Initialize kill count for Faction ID 2 (VS)
    };
  
    // Calculate the kill count for each faction based on player data
    players.forEach(player => {
      const factionID = player.faction_id;
      const cep = parseInt(player.cep);
      const kills = cep / 100; // Divide cep by 100 to get kills
  
      // Increment the kill count for the corresponding faction
      if (factionKillCounts.hasOwnProperty(factionID)) {
        factionKillCounts[factionID] += kills;
      }
    });
  
    // Sort the factions based on the kill count in descending order
    const sortedFactions = Object.keys(factionKillCounts).sort((a, b) => factionKillCounts[b] - factionKillCounts[a]);
  
    // Assign rank, faction ID, and kill count to each faction
    const rankedFactions = sortedFactions.map((factionID, index) => ({
      rank: index + 1,
      factionID: parseInt(factionID),
      killCount: factionKillCounts[factionID]
    }));
  
    return rankedFactions; // Return the ranked factions data
  }
  
  const result = calculateTotalKillCountByFaction(players);

  // Get the table element with the ID "empireTable"
  const table = document.getElementById("empireTable");
  
  // Create and append table rows with the ranked factions data
  result.forEach(faction => {
  const row = document.createElement("tr");
  const rankCell = document.createElement("td");
  const factionCell = document.createElement("td");
  const factionImage = document.createElement("img"); // Create an img element
  const killCountCell = document.createElement("td");

  rankCell.textContent = faction.rank;

  // Set the faction image source and alt text based on faction ID
  let factionImageSrc;
  let factionAltText;
  let rowColor;
  if (faction.factionID === 0) {
    factionImageSrc = "Images/Empires-tr-icon.webp"; // Replace with the image source for TR
    factionAltText = "TR";
    rowColor = "rgba(255, 0, 0, 0.2)"; // Transparent light red
  } else if (faction.factionID === 1) {
    factionImageSrc = "Images/Empires-nc-icon.webp"; // Replace with the image source for NC
    factionAltText = "NC";
    rowColor = "rgba(0, 0, 255, 0.2)"; // Transparent light blue
  } else if (faction.factionID === 2) {
    factionImageSrc = "Images/Empires-vs-icon.webp"; // Replace with the image source for VS
    factionAltText = "VS";
    rowColor = "rgba(128, 0, 128, 0.2)"; // Transparent light purple
  } else {
    factionImageSrc = "path/to/unknown_image.jpg"; // Replace with the image source for unknown faction
    factionAltText = "Unknown";
    rowColor = "transparent";
  }

  factionImage.src = factionImageSrc; // Set the src attribute of the img element to the faction image source
  factionImage.alt = factionAltText; // Set the alt attribute of the img element to provide alternative text

  factionImage.style.width = "15%"; // Set the width of the img element to 50% of its original size

  factionCell.appendChild(factionImage); // Append the img element to the faction cell
  killCountCell.textContent = faction.killCount;

  // Apply row color style
  row.style.backgroundColor = rowColor;

  // Append cells to the row
  row.appendChild(rankCell);
  row.appendChild(factionCell);
  row.appendChild(killCountCell);

  // Append the row to the table
  table.appendChild(row);

  document.getElementById("table-search").addEventListener("keyup", display_query);
  document.getElementById("table-search-clear").addEventListener("mousedown", clear_query);
});
  
}

  function calculateBR(bep) {
    const thresholds = [
        { "br": 1, "bepThreshold": 0 },
        { "br": 2, "bepThreshold": 1000 },
        { "br": 3, "bepThreshold": 3000 },
        { "br": 4, "bepThreshold": 7500 },
        { "br": 5, "bepThreshold": 15000 },
        { "br": 6, "bepThreshold": 30000 },
        { "br": 7, "bepThreshold": 45000 },
        { "br": 8, "bepThreshold": 67500 },
        { "br": 9, "bepThreshold": 101250 },
        { "br": 10, "bepThreshold": 126563 },
        { "br": 11, "bepThreshold": 158203 },
        { "br": 12, "bepThreshold": 197754 },
        { "br": 13, "bepThreshold": 247192 },
        { "br": 14, "bepThreshold": 308990 },
        { "br": 15, "bepThreshold": 386239 },
        { "br": 16, "bepThreshold": 482798 },
        { "br": 17, "bepThreshold": 603497 },
        { "br": 18, "bepThreshold": 754371 },
        { "br": 19, "bepThreshold": 942964 },
        { "br": 20, "bepThreshold": 1178705 },
        { "br": 21, "bepThreshold": 1438020 },
        { "br": 22, "bepThreshold": 1710301 },
        { "br": 23, "bepThreshold": 1988027 },
        { "br": 24, "bepThreshold": 2286231 },
        { "br": 25, "bepThreshold": 2583441 },
        { "br": 26, "bepThreshold": 2908442 },
        { "br": 27, "bepThreshold": 3237942 },
        { "br": 28, "bepThreshold": 3618442 },
        { "br": 29, "bepThreshold": 3988842 },
        { "br": 30, "bepThreshold": 4488542 },
        { "br": 31, "bepThreshold": 5027342 },
        { "br": 32, "bepThreshold": 5789642 },
        { "br": 33, "bepThreshold": 6861342 },
        { "br": 34, "bepThreshold": 8229242 },
        { "br": 35, "bepThreshold": 10000542 },
        { "br": 36, "bepThreshold": 11501741 },
        { "br": 37, "bepThreshold": 12982642 },
        { "br": 38, "bepThreshold": 14897142 },
        { "br": 39, "bepThreshold": 16894542 },
        { "br": 40, "bepThreshold": 19994542 },
        // Add more BR thresholds as needed
      ];
  
    for (let i = thresholds.length - 1; i >= 0; i--) {
      if (bep >= thresholds[i].bepThreshold) {
        return thresholds[i].br;
      }
    }
  
    return 0; // Default BR if no threshold matches
  }


  function calculateCR(cep) {
    const thresholds = [
        { "cr": 1, "cepThreshold": 10000 },
        { "cr": 2, "cepThreshold": 50000 },
        { "cr": 3, "cepThreshold": 150000 },
        { "cr": 4, "cepThreshold": 300000 },
        { "cr": 5, "cepThreshold": 600000 },
        // Add more CR thresholds as needed
      ];
  
    for (let i = thresholds.length - 1; i >= 0; i--) {
      if (cep >= thresholds[i].cepThreshold) {
        return thresholds[i].cr;
      }
    }
  
    return 0; // Default BR if no threshold matches
  }
  

 /* // Calculate and assign kills property to each player
  players.forEach(player => {
    player.kills = parseInt(player.cep) / 100;
  });
  */
  
/*
async function display_query() {
  for (var i = 0; i < players.length; i++) {
    if (players[i] != null) {
      var name = players[i].name;
      document.getElementById(`${name}`).style.display = "none";
      var match = document.getElementById("table-search").value;
      if (document.getElementById("table-search").textContent.match(`${name}`)) {
        document.getElementById(`${match}`).style.display = "contents";
      }
    }
  }
}*/
 

main();
