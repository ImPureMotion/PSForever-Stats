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
    const killCountCell = document.createElement("td");
  
    rankCell.textContent = faction.rank;
  
    // Set the faction name based on faction ID
    let factionName;
    let rowColor;
    if (faction.factionID === 0) {
      factionName = "TR";
      rowColor = "rgba(255, 0, 0, 0.2)"; // Transparent light red
    } else if (faction.factionID === 1) {
      factionName = "NC";
      rowColor = "rgba(0, 0, 255, 0.2)"; // Transparent light blue
    } else if (faction.factionID === 2) {
      factionName = "VS";
      rowColor = "rgba(128, 0, 128, 0.2)"; // Transparent light purple
    } else {
      factionName = "Unknown";
      rowColor = "transparent";
    }
  
    factionCell.textContent = factionName;
    killCountCell.textContent = faction.killCount;
  
    // Apply row color style
    row.style.backgroundColor = rowColor;
  
    // Append cells to the row
    row.appendChild(rankCell);
    row.appendChild(factionCell);
    row.appendChild(killCountCell);
  
    // Append the row to the table
    table.appendChild(row);
  });
  
  
  