import { api } from "../../../admin/js/libs/api.js";

const urlParams = new URLSearchParams(window.location.search);
const voteId = urlParams.get("id");
// c pour card mode 
const mode = urlParams.get("c"); 



document.addEventListener("DOMContentLoaded", async () => {
    
    try {
        const scrutin = await api.get(`poll/${voteId}`).then((data) => data);
        const container = document.getElementById("candidatsContainer");

        if (!scrutin || !scrutin.message || !scrutin.message.posts) {
            container.innerHTML = "<p>Impossible de charger ce scrutin.</p>";
            return;
        }

        console.log(scrutin);

        document.getElementById("title").innerText = `Scrutin: ${scrutin.message.title}`;

        // Afficher les postes et candidats
        scrutin.message.posts.forEach(poste => {

            const div = document.createElement("div");
            div.className = "card";
            div.innerHTML = `<h3>${poste.postName}</h3>`;

            if (poste.candidateList && poste.candidateList.length > 0) {
                poste.candidateList.forEach(c => {
                    const candidateDiv = document.createElement("div");
                    candidateDiv.className = "candidate";
                    candidateDiv.innerHTML = `
                        <label>
                            <input type="radio" name="poste-${poste.id}" value="${c.user_id}">
                            ${c.name}
                        </label>
                    `;
                    div.appendChild(candidateDiv);
                });
            } else {
                div.innerHTML += "<p>Aucun candidat pour ce poste.</p>";
            }

            container.appendChild(div);
        });

        // Gestion du vote
        const btnSubmit = document.getElementById("btnSubmitVote");
        btnSubmit.addEventListener("click", async () => {
            const selections = {}; 
            const radios = document.querySelectorAll("input[type=radio]:checked");

            radios.forEach(r => {
                console.log(r);
                const posteId = r.name.split("-")[1];
                selections[posteId] = r.value;

                console.log(selections);
            });

            if (Object.keys(selections).length === 0) {
                alert("Veuillez sélectionner au moins un candidat.");
                return;
            }

            try {
                // Envoyer chaque sélection individuellement selon l'API attendue
                let response;
                for (const [idPost, idCandidate] of Object.entries(selections)) {
                    const formData = new FormData();
                    formData.append("user_id", 2); 
                    formData.append("poll_id", parseInt(voteId));
                    formData.append("post_id", parseInt(idPost));
                    formData.append("candidate_id", parseInt(idCandidate));
                    response = await api.post("vote", formData);

                    console.log("Réponse du serveur pour le poste", idPost, ":", response);
                    if (!response || response.errors) break;
                }

                // Optionally, disable the submit button to prevent double submission
                btnSubmit.disabled = true;

                console.log(response);

                // Handle server-side validation errors
                if (response && response.errors) {
                    alert("Erreur(s) : " + response.errors.join(", "));
                    btnSubmit.disabled = false;
                    return;
                }

                if (response && response.status === "success") {
                    alert(`${response.message}`);
                    window.location.href = "scrutin.php";
                } else {
                    alert(`${response.message}`);
                }
            } catch (err) {
                console.error(err);
                alert(`${response.message}`);
            }
        });
    } catch (err) {
        console.error("Erreur API :", err);
        document.getElementById("candidatsContainer").innerHTML = "<p>Erreur de chargement des candidats.</p>";
    }
});
