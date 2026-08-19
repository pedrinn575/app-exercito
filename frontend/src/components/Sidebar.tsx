/**
 * Menu lateral de navegação.
 */

import { NavLink } from 'react-router-dom';

const menuItems = [
  { path: '/dashboard', label: 'Dashboard', icon: '📊' },
  { path: '/militares', label: 'Militares', icon: '👤' },
  { path: '/administradores', label: 'Administradores', icon: '🎖' },
  { path: '/escala-preta', label: 'Escala Preta', icon: '⬛' },
  { path: '/escala-vermelha', label: 'Escala Vermelha', icon: '🟥' },
  { path: '/trocas', label: 'Trocas', icon: '🔄' },
  { path: '/faltas', label: 'Faltas', icon: '❌' },
  { path: '/relatorios', label: 'Relatórios', icon: '📋' },
];

export function Sidebar() {
  return (
    <aside className="sidebar">
      <nav className="sidebar-nav">
        {menuItems.map((item) => (
          <NavLink
            key={item.path}
            to={item.path}
            className={({ isActive }) =>
              `sidebar-link ${isActive ? 'sidebar-link-active' : ''}`
            }
          >
            <span className="sidebar-icon">{item.icon}</span>
            {item.label}
          </NavLink>
        ))}
      </nav>
    </aside>
  );
}
