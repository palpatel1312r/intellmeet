import { useState } from "react";
import { Link, usePage } from "@inertiajs/react";
import { Dialog, Transition } from "@headlessui/react";
import {
    HomeIcon,
    VideoCameraIcon,
    CalendarIcon,
    ClipboardDocumentListIcon,
    Cog6ToothIcon,
    ArrowRightOnRectangleIcon,
} from "@heroicons/react/24/outline";

const navigation = [
    { name: "Dashboard", href: "/dashboard", icon: HomeIcon },
    { name: "Meetings", href: "/meetings", icon: VideoCameraIcon },
    { name: "Calendar", href: "/calendar", icon: CalendarIcon },
    { name: "Tasks", href: "/tasks", icon: ClipboardDocumentListIcon },
];

export default function Layout({ children }) {
    const { auth } = usePage().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);

    return (
        <div className="min-h-screen bg-gray-100">
            {/* Mobile sidebar */}
            <Transition show={sidebarOpen}>
                <Dialog
                    onClose={() => setSidebarOpen(false)}
                    className="lg:hidden"
                >
                    {/* Sidebar content */}
                </Dialog>
            </Transition>

            {/* Static sidebar for desktop */}
            <div className="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
                <div className="flex flex-col flex-grow bg-white pt-5 pb-4 overflow-y-auto">
                    <div className="flex items-center flex-shrink-0 px-4">
                        <h1 className="text-2xl font-bold text-indigo-600">
                            IntellMeet
                        </h1>
                    </div>
                    <nav className="mt-5 flex-1 px-2 space-y-1">
                        {navigation.map((item) => (
                            <Link
                                key={item.name}
                                href={item.href}
                                className="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            >
                                <item.icon className="mr-3 h-6 w-6" />
                                {item.name}
                            </Link>
                        ))}
                    </nav>
                    <div className="border-t border-gray-200 pt-4">
                        <div className="px-2">
                            <div className="flex items-center">
                                <img
                                    className="h-8 w-8 rounded-full"
                                    src={
                                        auth.user.avatar_url ||
                                        "https://ui-avatars.com/api/?name=" +
                                            auth.user.name
                                    }
                                    alt=""
                                />
                                <div className="ml-3">
                                    <p className="text-sm font-medium text-gray-700">
                                        {auth.user.name}
                                    </p>
                                    <p className="text-xs text-gray-500">
                                        {auth.user.email}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Main content */}
            <div className="lg:pl-64 flex flex-col flex-1">
                <main className="flex-1">
                    <div className="py-6">
                        <div className="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                            {children}
                        </div>
                    </div>
                </main>
            </div>
        </div>
    );
}
