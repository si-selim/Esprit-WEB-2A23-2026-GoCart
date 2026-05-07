<?php
// Ce fichier NE démarre PAS la session - elle doit être démarrée dans chaque page principale
function getCurrentUser() {
    if (isset($_SESSION['user'])) return $_SESSION['user'];
    return null;
}
function isAdmin() { $u = getCurrentUser(); return $u && $u['role'] === 'admin'; }
function isOrganisateur() { $u = getCurrentUser(); return $u && $u['role'] === 'organisateur'; }
function isParticipant() { $u = getCurrentUser(); return $u && $u['role'] === 'participant'; }
function isConnected() { return getCurrentUser() !== null; }
function getUserId() { $u = getCurrentUser(); return $u ? ($u['id'] ?? null) : null; }
